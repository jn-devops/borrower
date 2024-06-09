<?php

namespace Homeful\Borrower;

use Brick\Money\Money;
use Homeful\Borrower\Classes\DisposableModifier;
use Homeful\Borrower\Exceptions\MaximumBorrowingAgeBreached;
use Homeful\Borrower\Exceptions\MinimumBorrowingAgeNotMet;
use Homeful\Property\Property;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Whitecube\Price\Price;

class Borrower
{
    const MINIMUM_BORROWING_AGE = 18;

    const MAXIMUM_BORROWING_AGE = 60;

    protected Price $gross_monthly_income;

    protected bool $regional = false;

    protected Collection $co_borrowers;

    protected Carbon $birthdate;

    public int $maximum_age_at_loan_maturity = 70; //years old

    public function __construct()
    {
        $this->co_borrowers = new Collection;
    }

    /**
     * @return $this
     *
     * @throws MaximumBorrowingAgeBreached
     * @throws MinimumBorrowingAgeNotMet
     */
    public function setBirthdate(Carbon $value): self
    {
        if ((int) floor($value->diffInYears(Carbon::today())) < self::MINIMUM_BORROWING_AGE) {
            throw new MinimumBorrowingAgeNotMet;
        }
        if ((int) floor($value->diffInYears(Carbon::today())) > self::MAXIMUM_BORROWING_AGE) {
            throw new MaximumBorrowingAgeBreached;
        }
        $this->birthdate = $value;

        return $this;
    }

    public function getBirthdate(): Carbon
    {
        return $this->birthdate;
    }

    /**
     * @return $this
     */
    public function setRegional(bool $value): self
    {
        $this->regional = $value;

        return $this;
    }

    public function getRegional(): bool
    {
        return $this->regional;
    }

    /**
     * @return $this
     *
     * @throws \Brick\Math\Exception\NumberFormatException
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function addWages(Money|float $value): self
    {
        $this->gross_monthly_income = new Price(($value instanceof Money) ? $value : Money::of($value, 'PHP'));

        return $this;
    }

    /**
     * @return $this
     *
     * @throws \Brick\Math\Exception\NumberFormatException
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function addOtherSourcesOfIncome(string $name, Money|float $value): self
    {
        $this->gross_monthly_income->addModifier($name, ($value instanceof Money) ? $value : Money::of($value, 'PHP'));

        return $this;
    }

    public function getGrossMonthlyIncome(): Price
    {
        return $this->gross_monthly_income;
    }

    public function getDisposableMonthlyIncome(Property $property): Price
    {
        return (new Price($this->gross_monthly_income->inclusive()))
            ->addModifier('effective-value', DisposableModifier::class, $property);
    }

    /**
     * @return $this
     */
    public function addCoBorrower(Borrower $co_borrower): self
    {
        $this->co_borrowers->add($co_borrower);

        return $this;
    }

    public function getCoBorrowers(): Collection
    {
        return $this->co_borrowers;
    }

    public function getJointDisposableMonthlyIncome(Property $property): Price
    {
        $disposable_monthly_income = new Price($this->getDisposableMonthlyIncome($property)->inclusive());
        $this->co_borrowers->each(function (Borrower $co_borrower) use ($disposable_monthly_income, $property) {
            $disposable_monthly_income->addModifier('co-borrower', $co_borrower->getDisposableMonthlyIncome($property)->inclusive());
        });

        return $disposable_monthly_income;
    }

    public function getOldestAmongst(): Borrower
    {
        $oldest = $this;
        $this->co_borrowers->each(function (Borrower $co_borrower) use (&$oldest) {
            if ($co_borrower->getBirthdate()->lt($oldest->getBirthdate())) {
                $oldest = $co_borrower;
            }
        });

        return $oldest;
    }
}
