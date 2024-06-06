<?php

namespace Homeful\Borrower\Tests;

use Brick\Money\Money;
use Whitecube\Price\Price;

class MoneyTest extends TestCase
{
    public function test_money(): void
    {
        $money = Money::ofMinor(100, 'PHP');
        $this->assertEquals(1, $money->getAmount()->toInt());
        $money = Money::of(1, 'PHP');
        $this->assertEquals(1, $money->getAmount()->toInt());
        $this->assertEquals(100, $money->getMinorAmount()->toInt());
        $this->assertEquals(100, Money::of(1, 'PHP')->getMinorAmount()->toInt());
    }

    public function test_price(): void
    {
        $wages = 12000.00;
        $commissions = 2000.00;
        $price = Price::of($wages, 'PHP');
        $price->addModifier('commissions', Money::of($commissions, 'PHP'));
        $this->assertEquals($wages, $price->base()->getAmount()->toFloat());
        $this->assertEquals($wages + $commissions, $price->inclusive()->getAmount()->toFloat());
    }
}
