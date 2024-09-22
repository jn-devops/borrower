<?php

namespace Homeful\Borrower\Traits;

use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\UnknownCurrencyException;
use Homeful\Borrower\Classes\DisposableModifier;
use Brick\Math\Exception\NumberFormatException;
use Homeful\Borrower\Borrower;
use Homeful\Property\Property;
use Whitecube\Price\Price;
use Brick\Money\Money;

trait HasDeprecated
{
    /* @deprecated */
    const MINIMUM_BORROWING_AGE = 18;

    /* @deprecated */
    const MAXIMUM_BORROWING_AGE = 60;

    /**
     * @deprecated
     * @param Money|float $value
     * @return Borrower|HasDeprecated
     *
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     * @throws UnknownCurrencyException
     *
     */
    public function addWages(Money|float $value): self
    {
        $this->gross_monthly_income = new Price(($value instanceof Money) ? $value : Money::of($value, 'PHP'));

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
}
