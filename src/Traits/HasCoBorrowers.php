<?php

namespace Homeful\Borrower\Traits;

use Illuminate\Support\Collection;
use Homeful\Borrower\Borrower;
use Homeful\Property\Property;
use Brick\Math\RoundingMode;
use Whitecube\Price\Price;

trait HasCoBorrowers
{
    /**
     * @return $this
     */
    public function addCoBorrower(Borrower $co_borrower): self
    {
        $this->co_borrowers->add($co_borrower);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getCoBorrowers(): Collection
    {
        return $this->co_borrowers;
    }

    /**
     * @return Price
     */
    public function getJointMonthlyDisposableIncome(): Price
    {
        $monthly_disposable_income = new Price($this->getMonthlyDisposableIncome()->inclusive());
        $this->co_borrowers->each(function (Borrower $co_borrower) use ($monthly_disposable_income) {
            $monthly_disposable_income->addModifier('co-borrower disposable income', $co_borrower->getMonthlyDisposableIncome()->inclusive(), roundingMode: RoundingMode::CEILING);
        });

        return $monthly_disposable_income;
    }

//    /**
//     * @param Property|null $property
//     * @return Price
//     */
//    public function getJointMonthlyDisposableIncome(Property $property = null): Price
//    {
//        $property = $this->getProperty() ?: $property;
//        $monthly_disposable_income = new Price($this->getMonthlyDisposableIncome($property)->inclusive());
//        $this->co_borrowers->each(function (Borrower $co_borrower) use ($monthly_disposable_income, $property) {
//            $monthly_disposable_income->addModifier('co-borrower', $co_borrower->getMonthlyDisposableIncome($property)->inclusive(), roundingMode: RoundingMode::CEILING);
//        });
//
//        return $monthly_disposable_income;
//    }
}
