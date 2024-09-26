<?php

namespace Homeful\Borrower\Classes;

class AffordabilityRates
{
    public function __construct(public float $interest_rate, public int $repricing_frequency){}
}
