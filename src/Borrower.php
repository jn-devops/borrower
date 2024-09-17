<?php

namespace Homeful\Borrower;

use Homeful\Borrower\Exceptions\MaximumBorrowingAgeBreached;
use Homeful\Borrower\Exceptions\MinimumBorrowingAgeNotMet;
use Homeful\Borrower\Classes\DisposableModifier;
use Illuminate\Support\Collection;
use Homeful\Property\Property;
use Illuminate\Support\Carbon;
use Brick\Math\RoundingMode;
use Illuminate\Support\Str;
use Whitecube\Price\Price;

use Brick\Money\Money;

class Borrower
{
    const MINIMUM_BORROWING_AGE = 18;

    const MAXIMUM_BORROWING_AGE = 60;

    protected Price $gross_monthly_income;

    protected bool $regional = false;

    protected Collection $co_borrowers;

    protected Carbon $birthdate;

    public int $maximum_age_at_loan_maturity = 70; //years old

    public string $contact_id;

    public function __construct()
    {
        $this->co_borrowers = new Collection;
    }

    /**
     * @return $this
     */
    public function setContactId(string $contact_id): self
    {
        $this->contact_id = $contact_id;

        return $this;
    }

    public function getContactId(): string
    {
        return $this->contact_id ?? Str::uuid();
    }

    /**
     * @return $this
     *
     * @throws MaximumBorrowingAgeBreached
     * @throws MinimumBorrowingAgeNotMet
     */
    public function setBirthdate(Carbon $value): self
    {
        if ((int) floor($value->diffInYears(Carbon::now())) < self::MINIMUM_BORROWING_AGE) {
            throw new MinimumBorrowingAgeNotMet;
        }
        if ((int) floor($value->diffInYears(Carbon::now())) > self::MAXIMUM_BORROWING_AGE) {
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
     *
     * @throws MaximumBorrowingAgeBreached
     * @throws MinimumBorrowingAgeNotMet
     */
    public function setAge(int $years): self
    {
        $birthdate = Carbon::now()->addYears(-1 * $years);
        $this->setBirthdate($birthdate);

        return $this;
    }

    public function getAge(): float
    {
        return round($this->getBirthdate()->diffInYears(), 1, PHP_ROUND_HALF_UP);
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
        return $this->regional ?? config('borrower.default_regional');
    }

    /**
     * @deprecated
     *
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
    public function setGrossMonthlyIncome(Price|Money|float $value): self
    {
        $this->gross_monthly_income = ($value instanceof Price)
            ? $value
            : new Price(($value instanceof Money) ? $value : Money::of($value, 'PHP'));

        return $this;
    }

    public function getGrossMonthlyIncome(): Price
    {
        return $this->gross_monthly_income;
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

    /**
     * @deprecated
     */
    public function getDisposableMonthlyIncome(Property $property): Price
    {
        return (new Price($this->gross_monthly_income->inclusive()))
            ->addModifier('effective-value', DisposableModifier::class, $property);
    }

    public function getMonthlyDisposableIncome(Property $property): Price
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

    /**
     * @deprecated
     */
    public function getJointDisposableMonthlyIncome(Property $property): Price
    {
        $disposable_monthly_income = new Price($this->getDisposableMonthlyIncome($property)->inclusive());
        $this->co_borrowers->each(function (Borrower $co_borrower) use ($disposable_monthly_income, $property) {
            $disposable_monthly_income->addModifier('co-borrower', $co_borrower->getDisposableMonthlyIncome($property)->inclusive());
        });

        return $disposable_monthly_income;
    }

    public function getJointMonthlyDisposableIncome(Property $property): Price
    {
        $monthly_disposable_income = new Price($this->getMonthlyDisposableIncome($property)->inclusive());
        $this->co_borrowers->each(function (Borrower $co_borrower) use ($monthly_disposable_income, $property) {
            $monthly_disposable_income->addModifier('co-borrower', $co_borrower->getMonthlyDisposableIncome($property)->inclusive(), roundingMode: RoundingMode::CEILING);
        });

        return $monthly_disposable_income;
    }

    /**
     * @return $this
     */
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
