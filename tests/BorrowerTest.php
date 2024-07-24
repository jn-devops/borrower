<?php

use Brick\Money\Money;
use Homeful\Borrower\Borrower;
use Homeful\Borrower\Data\BorrowerData;
use Homeful\Borrower\Exceptions\MaximumBorrowingAgeBreached;
use Homeful\Borrower\Exceptions\MinimumBorrowingAgeNotMet;
use Homeful\Property\Property;
use Illuminate\Support\Carbon;
use Whitecube\Price\Price;

beforeEach(function () {
    $this->multiplier = 0.32;
});

dataset('property', function () {
    return [
        [fn () => (new Property)->setTotalContractPrice(Price::of(849999, 'PHP'))->setDisposableIncomeRequirementMultiplier($this->multiplier)],
    ];
});

dataset('borrower', function () {
    return [
        [fn () => (new Borrower)->setBirthdate(Carbon::parse('1999-03-17'))],
    ];
});

dataset('borrower_with_co-borrowers', function () {
    return [
        [fn () => (new Borrower)->setBirthdate(Carbon::parse('1999-03-17'))->setGrossMonthlyIncome(Money::of(15000.0, 'PHP'))
            ->addCoBorrower((new Borrower)->setBirthdate(Carbon::parse('2001-03-17'))->setGrossMonthlyIncome(Money::of(14000.0, 'PHP')))
            ->addCoBorrower((new Borrower)->setBirthdate(Carbon::parse('2000-03-17'))->setGrossMonthlyIncome(Money::of(13000.0, 'PHP'))),
        ],
    ];
});

it('has age', function () {
    $borrower = (new Borrower)->setBirthdate(Carbon::parse('1999-03-17'));
    expect($borrower->getAge())->toBe(round(Carbon::parse('1999-03-17')->diffInYears(Carbon::now()), 1, PHP_ROUND_HALF_UP));
});

it('can set age', function () {
    $borrower = (new Borrower)->setAge(54);
    expect($borrower->getAge())->toBe(54.0);
    expect($borrower->getBirthdate()->format('Y-m-d'))->toBe(Carbon::now()->addYears(-54)->format('Y-m-d'));
});

it('has default regional', function () {
    $borrower = new Borrower;
    expect($borrower->getRegional())->toBe(config('borrower.default_regional'));
});

it('can have co-borrowers, and can determine the youngest amongst', function () {
    $borrower = (new Borrower)->setBirthdate(Carbon::parse('1999-03-17'));
    $co_borrower[0] = (new Borrower)->setBirthdate(Carbon::parse('2001-03-17'));
    $co_borrower[1] = (new Borrower)->setBirthdate(Carbon::parse('2000-03-17'));
    $borrower->addCoBorrower($co_borrower[0])->addCoBorrower($co_borrower[1]);
    $borrower->getCoBorrowers()->each(function ($value, $index) use ($co_borrower) {
        expect($value)->toBe($co_borrower[$index]);
    });
    expect($borrower->getOldestAmongst())->toBe($borrower);
});

it('has gross monthly income, monthly disposable income and joint monthly disposable income', function (Borrower $borrower, Property $property) {
    expect($borrower->getGrossMonthlyIncome()->compareTo(Money::of(15000.0, 'PHP')))->toBe(0);
    expect($property->getTotalContractPrice()->inclusive()->getAmount()->toFloat())->toBe(849999.0);
    expect($borrower->getDisposableMonthlyIncome($property)
        ->compareTo($borrower->getGrossMonthlyIncome()->inclusive()->multipliedBy($property->getDisposableIncomeRequirementMultiplier())))
        ->toBe(0);
    expect($borrower->getDisposableMonthlyIncome($property)->inclusive()->getAmount()->toFloat())->toBe(4800.0);
    $borrower->getCoBorrowers()->each(function ($co_borrower, $index) use ($property) {
        match ($index) {
            0 => expect($co_borrower->getDisposableMonthlyIncome($property)->inclusive()->getAmount()->toFloat())->toBe(4480.0),
            1 => expect($co_borrower->getDisposableMonthlyIncome($property)->inclusive()->getAmount()->toFloat())->toBe(4160.0),
            default => null
        };
    });
    expect($borrower->getJointDisposableMonthlyIncome($property)->inclusive()->getAmount()->toFloat())->toBe(4800.0 + 4480.0 + 4160.0);
})->with('borrower_with_co-borrowers', 'property');

it('has monthly income and disposable monthly income', function (Property $property) {
    $borrower = new Borrower;
    $borrower->setGrossMonthlyIncome($salary = Money::of(12000.0, 'PHP'));
    $borrower->addOtherSourcesOfIncome('commissions', $commissions = Money::of(2000.0, 'PHP'));
    expect($borrower->getGrossMonthlyIncome()->base()->compareTo($salary))->toBe(0);
    expect($borrower->getGrossMonthlyIncome()->inclusive()->compareTo($salary->plus($commissions)))->toBe(0);
    expect($borrower->getDisposableMonthlyIncome($property)
        ->compareTo($borrower->getGrossMonthlyIncome()->inclusive()->multipliedBy($property->getDisposableIncomeRequirementMultiplier())))
        ->toBe(0);
})->with('property');

it('has borrowing ages', function () {
    $age = Borrower::MINIMUM_BORROWING_AGE;
    $birthdate = Carbon::now()->addYears(-1 * $age);
    $borrower = (new Borrower)->setBirthdate($birthdate);
    expect((int) floor($borrower->getBirthdate()->diffInYears()))->toBe($age);

    $age = Borrower::MAXIMUM_BORROWING_AGE;
    $birthdate = Carbon::now()->addYears(-1 * $age);
    $borrower = (new Borrower)->setBirthdate($birthdate);
    expect((int) floor($borrower->getBirthdate()->diffInYears()))->toBe($age);
});

it('has a legal age', function () {
    $borrower = new Borrower;
    $years = Borrower::MINIMUM_BORROWING_AGE - 1;
    $birthdate = Carbon::today()->addYears(-$years);
    $borrower->setBirthdate($birthdate);
})->expectException(MinimumBorrowingAgeNotMet::class);

it('has a retirement age', function () {
    $borrower = new Borrower;
    $years = Borrower::MAXIMUM_BORROWING_AGE + 1;
    $birthdate = Carbon::today()->addYears(-$years);
    $borrower->setBirthdate($birthdate);
})->expectException(MaximumBorrowingAgeBreached::class);

it('has borrower data', function (Borrower $borrower) {
    $borrower = new Borrower;
    $borrower->setBirthdate(Carbon::parse('1999-03-17'))->setGrossMonthlyIncome($salary = Money::of(12000.0, 'PHP'));
    $data = BorrowerData::fromObject($borrower);
    expect($data->gross_monthly_income)->toBe($borrower->getGrossMonthlyIncome()->inclusive()->getAmount()->toFloat());
    expect($data->regional)->toBe($borrower->getRegional());
    expect($data->birthdate)->toBe($borrower->getBirthdate()->format('Y-m-d'));
    expect($data->all())->toBe([
        'gross_monthly_income' => $borrower->getGrossMonthlyIncome()->inclusive()->getAmount()->toFloat(),
        'regional' => $borrower->getRegional(),
        'birthdate' => $borrower->getBirthdate()->format('Y-m-d'),
        'age' => $borrower->getAge(),
        'as_of_date' => Carbon::today()->format('Y-m-d'),
    ]);
})->with('borrower');
