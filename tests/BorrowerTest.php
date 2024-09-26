<?php

use Homeful\Borrower\Exceptions\MaximumBorrowingAgeBreached;
use Homeful\Borrower\Exceptions\MinimumBorrowingAgeNotMet;
use Homeful\Borrower\Classes\AffordabilityRates;
use Homeful\Common\Classes\{Assert, Input};
use Homeful\Borrower\Enums\EmploymentType;
use Homeful\Borrower\Data\BorrowerData;
use Homeful\Borrower\Enums\PaymentMode;
use Homeful\Common\Enums\WorkArea;
use Homeful\Borrower\Borrower;
use Homeful\Property\Property;
use Illuminate\Support\Carbon;
use Whitecube\Price\Price;
use Brick\Money\Money;

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

it('has age and formatted age', function () {
    $borrower = (new Borrower)->setBirthdate(Carbon::parse('1999-03-17'));
    expect($borrower->getFormattedAge(Carbon::parse('2029-03-17')))->toBe('30 years old');
    expect($borrower->getAge())->toBe(round(Carbon::parse('1999-03-17')->diffInYears(Carbon::now()), 1, PHP_ROUND_HALF_UP));
});

it('can set age', function () {
    $borrower = (new Borrower)->setAge(54);
    expect($borrower->getAge())->toBe(54.0);
    expect($borrower->getBirthdate()->format('Y-m-d'))->toBe(Carbon::now()->addYears(-54)->format('Y-m-d'));
});

it('has regional, work area', function () {
    $borrower = new Borrower;
    expect($borrower->getRegional())->toBe(config('borrower.default_regional'));
    expect(config('borrower.default_regional'))->toBe(false);
    expect($borrower->getRegional())->toBe(false);
    expect($borrower->getWorkArea())->toBe(WorkArea::HUC);
    $borrower->setRegional(true);
    expect($borrower->getWorkArea())->toBe(WorkArea::REGION);
    $borrower->setWorkArea(WorkArea::HUC);
    expect($borrower->getRegional())->toBeFalse();
    $borrower->setWorkArea(WorkArea::REGION);
    expect($borrower->getRegional())->toBeTrue();
});

it('has employment type', function () {
    $borrower = new Borrower;
    expect($borrower->getEmploymentType())->toBe(EmploymentType::LOCAL_PRIVATE);
    $borrower->setEmploymentType(EmploymentType::LOCAL_GOVERNMENT);
    expect($borrower->getEmploymentType())->toBe(EmploymentType::LOCAL_GOVERNMENT);
    $borrower->setEmploymentType(EmploymentType::OFW);
    expect($borrower->getEmploymentType())->toBe(EmploymentType::OFW);
    $borrower->setEmploymentType(EmploymentType::BUSINESS);
    expect($borrower->getEmploymentType())->toBe(EmploymentType::BUSINESS);
    $borrower->setEmploymentType(EmploymentType::LOCAL_PRIVATE);
    expect($borrower->getEmploymentType())->toBe(EmploymentType::LOCAL_PRIVATE);
});

it('has payment mode', function () {
    $borrower = new Borrower;
    expect($borrower->getPaymentMode())->toBe(PaymentMode::ONLINE);
    $borrower->setPaymentMode(PaymentMode::SALARY_DEDUCTION);
    expect($borrower->getPaymentMode())->toBe(PaymentMode::SALARY_DEDUCTION);
    $borrower->setPaymentMode(PaymentMode::OVER_THE_COUNTER);
    expect($borrower->getPaymentMode())->toBe(PaymentMode::OVER_THE_COUNTER);
    $borrower->setPaymentMode(PaymentMode::ONLINE);
    expect($borrower->getPaymentMode())->toBe(PaymentMode::ONLINE);
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
    expect($borrower->getMonthlyDisposableIncome($property)
        ->compareTo($borrower->getGrossMonthlyIncome()->inclusive()->multipliedBy($property->getDisposableIncomeRequirementMultiplier())))
        ->toBe(0);
    expect($borrower->getMonthlyDisposableIncome($property)->inclusive()->getAmount()->toFloat())->toBe(4800.0);
    $borrower->getCoBorrowers()->each(function ($co_borrower, $index) use ($property) {
        match ($index) {
            0 => expect($co_borrower->getMonthlyDisposableIncome($property)->inclusive()->getAmount()->toFloat())->toBe(4480.0),
            1 => expect($co_borrower->getMonthlyDisposableIncome($property)->inclusive()->getAmount()->toFloat())->toBe(4160.0),
            default => null
        };
    });
    expect($borrower->getJointMonthlyDisposableIncome($property)->inclusive()->getAmount()->toFloat())->toBe(4800.0 + 4480.0 + 4160.0);
})->with('borrower_with_co-borrowers', 'property');

it('has monthly income and disposable monthly income', function (Property $property) {
    $borrower = new Borrower;
    $borrower->setGrossMonthlyIncome($salary = Money::of(12000.0, 'PHP'));
    $borrower->addOtherSourcesOfIncome('commissions', $commissions = Money::of(2000.0, 'PHP'));
    expect($borrower->getGrossMonthlyIncome()->base()->compareTo($salary))->toBe(0);
    expect($borrower->getGrossMonthlyIncome()->inclusive()->compareTo($salary->plus($commissions)))->toBe(0);
    expect($borrower->getMonthlyDisposableIncome($property)
        ->compareTo($borrower->getGrossMonthlyIncome()->inclusive()->multipliedBy($property->getDisposableIncomeRequirementMultiplier())))
        ->toBe(0);
})->with('property');

it('has borrowing ages', function () {
    expect(config('borrower.borrowing_age.minimum'))->toBe(18);
    expect(Borrower::getMinimumBorrowingAge())->toBe(config('borrower.borrowing_age.minimum'));
    expect(config('borrower.borrowing_age.maximum.hdmf'))->toBe(60);
    expect(config('borrower.borrowing_age.maximum.rcbc'))->toBe(60);
    expect(config('borrower.borrowing_age.maximum.cbc'))->toBe(60);
    expect(config('borrower.borrowing_age.maximum.default'))->toBe(60);
    expect(Borrower::getMaximumBorrowingAge('hdmf'))->toBe(config('borrower.borrowing_age.maximum.hdmf'));
    expect(Borrower::getMaximumBorrowingAge('rcbc'))->toBe(config('borrower.borrowing_age.maximum.rcbc'));
    expect(Borrower::getMaximumBorrowingAge('cbc'))->toBe(config('borrower.borrowing_age.maximum.cbc'));
    expect(Borrower::getMaximumBorrowingAge())->toBe(config('borrower.borrowing_age.maximum.default'));

    $age = Borrower::getMinimumBorrowingAge();
    $birthdate = Carbon::now()->addYears(-1 * $age);
    $borrower = (new Borrower)->setBirthdate($birthdate);
    expect((int) floor($borrower->getBirthdate()->diffInYears()))->toBe($age);

    $age = Borrower::getMaximumBorrowingAge();
    $birthdate = Carbon::now()->addYears(-1 * $age);
    $borrower = (new Borrower)->setBirthdate($birthdate);
    expect((int) floor($borrower->getBirthdate()->diffInYears()))->toBe($age);
});

it('has a legal age', function () {
    $borrower = new Borrower;
    $years = Borrower::getMinimumBorrowingAge() - 1;
    $birthdate = Carbon::today()->addYears(-$years);
    $borrower->setBirthdate($birthdate);
})->expectException(MinimumBorrowingAgeNotMet::class);

it('has a retirement age', function () {
    $borrower = new Borrower;
    $years = Borrower::getMaximumBorrowingAge() + 1;
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
        'work_area' => $borrower->getWorkArea()->getName(),
        'employment_type' => $borrower->getEmploymentType()->getName(),
        'formatted_age' => $borrower->getFormattedAge(),
        'payment_mode' => $borrower->getPaymentMode()->getName()
    ]);
})->with('borrower');

it('has contact id', function () {
    $borrower = new Borrower;
    expect($borrower->getContactId())->toBeUuid();
    $contact_id = 'ABC-123';
    $borrower->setContactId($contact_id);
    expect($borrower->getContactId())->toBe($contact_id);
});


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

it('has affordability rates', function (array $params) {
    $borrower = new Borrower;
    $borrower->setGrossMonthlyIncome($params[Input::WAGES]);
    $borrower->setWorkArea($params[Input::WORK_AREA]);
    with($borrower->getAffordabilityRates(), function (AffordabilityRates $rates) use ($params) {
        expect($rates->interest_rate)->toBe($params[Assert::INTEREST_RATE]);
        expect($rates->repricing_frequency)->toBe($params[Assert::REPRICING_FREQUENCY]);
    });
})->with('affordability matrix');
