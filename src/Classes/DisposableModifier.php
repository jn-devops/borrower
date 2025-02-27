<?php

namespace Homeful\Borrower\Classes;

use Whitecube\Price\PriceAmendable;
use Homeful\Borrower\Borrower;
use Brick\Money\AbstractMoney;
use Brick\Math\RoundingMode;
use Whitecube\Price\Vat;
use Brick\Money\Money;

class DisposableModifier implements PriceAmendable
{
    protected string $type;

    protected Borrower $borrower;

    public function __construct(Borrower $borrower)
    {
        $this->borrower = $borrower;
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
            'modifier' => 'disposable income multiplier',
            'disposable_income_multiplier' => $this->borrower->getDisposableIncomeMultiplier(),
            'default_disposable_income_multiplier' => config('borrower.default_disposable_income_multiplier'),
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
        $disposable_income_multiplier = $this->borrower->getDisposableIncomeMultiplier(); //TODO: get min of property disposable income requirement

        if ($build instanceof Money) {
            return $build->multipliedBy($disposable_income_multiplier, roundingMode: RoundingMode::CEILING);
        } else {
            return null;
        }
    }
}
