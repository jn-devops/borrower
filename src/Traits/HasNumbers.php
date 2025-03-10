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

    public function getDisposableIncomeMultiplier(): float
    {
        return $this->disposable_income_multiplier ?? config('borrower.default_disposable_income_multiplier', 0.35);//TODO: get required disposable income multiplier instead if property is present
    }

    public function setDisposableIncomeMultiplier(float $disposable_income_multiplier): self
    {
        $this->disposable_income_multiplier = $disposable_income_multiplier;

        return $this;
    }

    /**
     * @return Price
     */
    public function getMonthlyDisposableIncome(): Price
    {
        return (new Price($this->getGrossMonthlyIncome()->inclusive()))
            ->addModifier('disposable income multiplier', DisposableModifier::class, $this);
//        return (new Price($this->gross_monthly_income->inclusive()))
//            ->addModifier('disposable income multiplier', DisposableModifier::class, $this);
    }

    /**
     * @return AffordabilityRates
     */
    public function getAffordabilityRates(): AffordabilityRates
    {
        return AffordabilityRates::defaultFromWork($this->getWorkArea(), $this->getGrossMonthlyIncome());
    }

    //    protected function getDefaultAnnualInterestRate(Price $total_contract_price, Price $gross_monthly_income, bool $regional): float
//    {
//        $tcp = $total_contract_price->inclusive()->getAmount()->toFloat();
//        $gmi = $gross_monthly_income->inclusive()->getAmount()->toFloat();
//
//        return match($this->getMarketSegment()) {
//            MarketSegment::SOCIALIZED, MarketSegment::ECONOMIC => match (true) {
//                $tcp <= 750000 => $regional
//                    ? ($gmi <= 12000 ? 0.030 : 0.0625)
//                    : ($gmi <= 14500 ? 0.030 : 0.0625),
//                $tcp <= 800000 => $regional
//                    ? ($gmi <= 13000 ? 0.030 : 0.0625)
//                    : ($gmi <= 15500 ? 0.030 : 0.0625),
//                $tcp <= 850000 => $regional
//                    ? ($gmi <= 15000 ? 0.030 : 0.0625)
//                    : ($gmi <= 16500 ? 0.030 : 0.0625),
//                default => 0.0625,
//            },
//            MarketSegment::OPEN => 0.07,
//        };
//    }
}
