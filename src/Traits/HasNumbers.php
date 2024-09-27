<?php

namespace Homeful\Borrower\Traits;

use Homeful\Borrower\Classes\DisposableModifier;
use Homeful\Borrower\Classes\AffordabilityRates;
use Homeful\Common\Enums\WorkArea;
use Homeful\Property\Property;
use Whitecube\Price\Price;
use Brick\Money\Money;

trait HasNumbers
{
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

    /**
     * @return Price
     */
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
     * @param Property $property
     * @return Price
     */
    public function getMonthlyDisposableIncome(Property $property): Price
    {
        return (new Price($this->gross_monthly_income->inclusive()))
            ->addModifier('effective-value', DisposableModifier::class, $property);
    }

    /**
     * @return AffordabilityRates
     */
    public function getAffordabilityRates(): AffordabilityRates
    {
        return AffordabilityRates::defaultFromWork($this->getWorkArea(), $this->getGrossMonthlyIncome());
    }
}
