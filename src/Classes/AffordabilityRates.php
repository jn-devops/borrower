<?php

namespace Homeful\Borrower\Classes;

use Homeful\Common\Enums\WorkArea;
use Whitecube\Price\Price;
use Brick\Money\Money;

class AffordabilityRates
{
    public function __construct(protected float $interest_rate, protected int $repricing_frequency){}

    public static function defaultFromWork(WorkArea $work_area, Price|Money|float $gross_monthly_income): AffordabilityRates
    {
        $value = $gross_monthly_income instanceof Price
            ? $gross_monthly_income
            : new Price($gross_monthly_income instanceof Money
                ? $gross_monthly_income
                : Money::of($gross_monthly_income, 'PHP')
            );
        $gmi = $value->inclusive()->getAmount()->toFloat();

        return match($work_area) {
            WorkArea::HUC => match(true) {
                $gmi <=  15000.0 => new AffordabilityRates(0.0300, 3),
                $gmi <=  17500.0 => new AffordabilityRates(0.0650, 5),
                default => new AffordabilityRates(0.0625, 3)
            },
            WorkArea::REGION => match(true) {
                $gmi <=  12000.0 => new AffordabilityRates(0.0300, 3),
                $gmi <=  14000.0 =>  new AffordabilityRates(0.0650, 5),
                default => new AffordabilityRates(0.0625, 3)
            },
        };
    }

    protected function setInterestRate(float $interest_rate): AffordabilityRates
    {
        $this->interest_rate = $interest_rate;

        return $this;
    }

    public function getInterestRate(): float
    {
        return $this->interest_rate;
    }

    public function setRepricingFrequency(int $repricing_frequency): AffordabilityRates
    {
        $this->repricing_frequency = $repricing_frequency;
        $interest_rate = match (true) {
            $repricing_frequency < 3  => 0.05750,
            $repricing_frequency < 5  => 0.06250,
            $repricing_frequency < 10 => 0.06500,
            $repricing_frequency < 15 => 0.07125,
            $repricing_frequency < 20 => 0.07750,
            $repricing_frequency < 25 => 0.08500,
            $repricing_frequency < 30 => 0.09125,
            default => 0.09750
        };
        $this->setInterestRate($interest_rate);

        return $this;
    }

    public function getRepricingFrequency(): int
    {
        return $this->repricing_frequency;
    }
}
