<?php

namespace Homeful\Borrower\Classes;

use Brick\Money\AbstractMoney;
use Brick\Money\Money;
use Homeful\Property\Property;
use Whitecube\Price\PriceAmendable;
use Whitecube\Price\Vat;

class DisposableModifier implements PriceAmendable
{
    protected string $type;

    protected Property $property;

    public function __construct(Property $property)
    {
        $this->property = $property;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function setType(?string $type = null): static
    {
        $this->type = $type;

        return $this;
    }

    public function key(): ?string
    {
        return 'disposable';
    }

    public function attributes(): ?array
    {
        return [
            'market_segment' => $this->property->getMarketSegment()->getName(),
            'default_disposable_income_requirement_multiplier' => $this->property->getDefaultDisposableIncomeRequirementMultiplier(),
            'disposable_income_requirement_multiplier' => $this->property->getDisposableIncomeRequirementMultiplier(),
        ];
    }

    public function appliesAfterVat(): bool
    {
        return false;
    }

    /**
     * @throws \Brick\Math\Exception\MathException
     */
    public function apply(AbstractMoney $build, float $units, bool $perUnit, ?AbstractMoney $exclusive = null, ?Vat $vat = null): ?AbstractMoney
    {
        if ($build instanceof Money) {
            return $build->multipliedBy($this->property->getDisposableIncomeRequirementMultiplier());
        } else {
            return null;
        }
    }
}
