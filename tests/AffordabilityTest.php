<?php

use Homeful\Borrower\Classes\AffordabilityRates;
use Homeful\Common\Classes\{Assert, Input};
use Homeful\Common\Enums\WorkArea;

dataset('affordability matrix', function () {
    return [
        fn() => [Input::WORK_AREA => WorkArea::REGION, Input::WAGES => 12000, Assert::REPRICING_FREQUENCY => 3, Assert::INTEREST_RATE => 0.0300],
        fn() => [Input::WORK_AREA => WorkArea::REGION, Input::WAGES => 14000, Assert::REPRICING_FREQUENCY => 5, Assert::INTEREST_RATE => 0.0650],
        fn() => [Input::WORK_AREA => WorkArea::REGION, Input::WAGES => 14001, Assert::REPRICING_FREQUENCY => 3, Assert::INTEREST_RATE => 0.0625],
        fn() => [Input::WORK_AREA => WorkArea::HUC, Input::WAGES => 15000, Assert::REPRICING_FREQUENCY => 3, Assert::INTEREST_RATE => 0.0300],
        fn() => [Input::WORK_AREA => WorkArea::HUC, Input::WAGES => 17500, Assert::REPRICING_FREQUENCY => 5, Assert::INTEREST_RATE => 0.0650],
        fn() => [Input::WORK_AREA => WorkArea::HUC, Input::WAGES => 17501, Assert::REPRICING_FREQUENCY => 3, Assert::INTEREST_RATE => 0.0625],
    ];
});

it('has affordability rates', function (array $defaults) {
    with(AffordabilityRates::defaultFromWork($defaults[Input::WORK_AREA], $defaults[Input::WAGES]), function (AffordabilityRates $rates) use ($defaults) {
        expect($rates->getInterestRate())->toBe($defaults[Assert::INTEREST_RATE]);
        expect($rates->getRepricingFrequency())->toBe($defaults[Assert::REPRICING_FREQUENCY]);
    });
})->with('affordability matrix');

dataset('repricing matrix', function () {
    return [
        fn() => [Input::REPRICING_FREQUENCY => 1,  Assert::INTEREST_RATE => 0.05750],
        fn() => [Input::REPRICING_FREQUENCY => 2,  Assert::INTEREST_RATE => 0.05750],
        fn() => [Input::REPRICING_FREQUENCY => 3,  Assert::INTEREST_RATE => 0.06250],
        fn() => [Input::REPRICING_FREQUENCY => 4,  Assert::INTEREST_RATE => 0.06250],
        fn() => [Input::REPRICING_FREQUENCY => 5,  Assert::INTEREST_RATE => 0.06500],
        fn() => [Input::REPRICING_FREQUENCY => 6,  Assert::INTEREST_RATE => 0.06500],
        fn() => [Input::REPRICING_FREQUENCY => 7,  Assert::INTEREST_RATE => 0.06500],
        fn() => [Input::REPRICING_FREQUENCY => 8,  Assert::INTEREST_RATE => 0.06500],
        fn() => [Input::REPRICING_FREQUENCY => 9,  Assert::INTEREST_RATE => 0.06500],
        fn() => [Input::REPRICING_FREQUENCY => 10, Assert::INTEREST_RATE => 0.07125],
        fn() => [Input::REPRICING_FREQUENCY => 11, Assert::INTEREST_RATE => 0.07125],
        fn() => [Input::REPRICING_FREQUENCY => 12, Assert::INTEREST_RATE => 0.07125],
        fn() => [Input::REPRICING_FREQUENCY => 13, Assert::INTEREST_RATE => 0.07125],
        fn() => [Input::REPRICING_FREQUENCY => 14, Assert::INTEREST_RATE => 0.07125],
        fn() => [Input::REPRICING_FREQUENCY => 15, Assert::INTEREST_RATE => 0.07750],
        fn() => [Input::REPRICING_FREQUENCY => 16, Assert::INTEREST_RATE => 0.07750],
        fn() => [Input::REPRICING_FREQUENCY => 17, Assert::INTEREST_RATE => 0.07750],
        fn() => [Input::REPRICING_FREQUENCY => 18, Assert::INTEREST_RATE => 0.07750],
        fn() => [Input::REPRICING_FREQUENCY => 19, Assert::INTEREST_RATE => 0.07750],
        fn() => [Input::REPRICING_FREQUENCY => 20, Assert::INTEREST_RATE => 0.08500],
        fn() => [Input::REPRICING_FREQUENCY => 21, Assert::INTEREST_RATE => 0.08500],
        fn() => [Input::REPRICING_FREQUENCY => 22, Assert::INTEREST_RATE => 0.08500],
        fn() => [Input::REPRICING_FREQUENCY => 23, Assert::INTEREST_RATE => 0.08500],
        fn() => [Input::REPRICING_FREQUENCY => 24, Assert::INTEREST_RATE => 0.08500],
        fn() => [Input::REPRICING_FREQUENCY => 25, Assert::INTEREST_RATE => 0.09125],
        fn() => [Input::REPRICING_FREQUENCY => 26, Assert::INTEREST_RATE => 0.09125],
        fn() => [Input::REPRICING_FREQUENCY => 27, Assert::INTEREST_RATE => 0.09125],
        fn() => [Input::REPRICING_FREQUENCY => 28, Assert::INTEREST_RATE => 0.09125],
        fn() => [Input::REPRICING_FREQUENCY => 29, Assert::INTEREST_RATE => 0.09125],
        fn() => [Input::REPRICING_FREQUENCY => 30, Assert::INTEREST_RATE => 0.09750],
    ];
});

it('has a reprising index', function (array $params) {
    $rate = new AffordabilityRates(0.0, 0);
    $rate->setRepricingFrequency($params[Input::REPRICING_FREQUENCY]);
    expect($rate->getInterestRate())->toBe($params[Assert::INTEREST_RATE]);
})->with('repricing matrix');
