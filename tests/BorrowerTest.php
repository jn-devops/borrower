<?php

use Homeful\Borrower\Exceptions\MaximumBorrowingAgeBreached;
use Homeful\Borrower\Exceptions\MinimumBorrowingAgeNotMet;
use Homeful\Borrower\Classes\LendingInstitution;
use Homeful\Borrower\Exceptions\BirthdateNotSet;
use Homeful\Borrower\Enums\EmploymentType;
use Homeful\Borrower\Data\BorrowerData;
use Homeful\Borrower\Enums\PaymentMode;
use Homeful\Common\Classes\Amount;
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

dataset('borrower_with_co-borrowers', function () {
    return [
        [fn () => (new Borrower)->setBirthdate(Carbon::parse('1999-03-17'))->setGrossMonthlyIncome(Money::of(15000.0, 'PHP'))
            ->addCoBorrower((new Borrower)->setBirthdate(Carbon::parse('2001-03-17'))->setGrossMonthlyIncome(Money::of(14000.0, 'PHP')))
            ->addCoBorrower((new Borrower)->setBirthdate(Carbon::parse('2000-03-17'))->setGrossMonthlyIncome(Money::of(13000.0, 'PHP'))),
        ],
    ];
});

it('has age and formatted age', function (Property $property) {
    $borrower = (new Borrower($property))->setBirthdate(Carbon::parse('1999-03-17'));
    expect($borrower->getFormattedAge(Carbon::parse('2029-03-17')))->toBe('30 years old');
    expect($borrower->getAge())->toBe(round(Carbon::parse('1999-03-17')->diffInYears(Carbon::now()), 1, PHP_ROUND_HALF_UP));
})->with('property');

it('can set age', function (Property $property) {
    $borrower = (new Borrower($property))->setAge(54);
    expect($borrower->getAge())->toBe(54.0);
    expect($borrower->getBirthdate()->format('Y-m-d'))->toBe(Carbon::now()->addYears(-54)->format('Y-m-d'));
})->with('property');

it('has regional, work area', function (Property $property) {
    $borrower = new Borrower($property);
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
})->with('property');

it('has employment type', function (Property $property) {
    $borrower = new Borrower($property);
    expect($borrower->getEmploymentType())->toBe(EmploymentType::LOCAL_PRIVATE);
    $borrower->setEmploymentType(EmploymentType::LOCAL_GOVERNMENT);
    expect($borrower->getEmploymentType())->toBe(EmploymentType::LOCAL_GOVERNMENT);
    $borrower->setEmploymentType(EmploymentType::OFW);
    expect($borrower->getEmploymentType())->toBe(EmploymentType::OFW);
    $borrower->setEmploymentType(EmploymentType::BUSINESS);
    expect($borrower->getEmploymentType())->toBe(EmploymentType::BUSINESS);
    $borrower->setEmploymentType(EmploymentType::LOCAL_PRIVATE);
    expect($borrower->getEmploymentType())->toBe(EmploymentType::LOCAL_PRIVATE);
})->with('property');

it('has payment mode', function (Property $property) {
    $borrower = new Borrower($property);
    expect($borrower->getPaymentMode())->toBe(PaymentMode::ONLINE);
    $borrower->setPaymentMode(PaymentMode::SALARY_DEDUCTION);
    expect($borrower->getPaymentMode())->toBe(PaymentMode::SALARY_DEDUCTION);
    $borrower->setPaymentMode(PaymentMode::OVER_THE_COUNTER);
    expect($borrower->getPaymentMode())->toBe(PaymentMode::OVER_THE_COUNTER);
    $borrower->setPaymentMode(PaymentMode::ONLINE);
    expect($borrower->getPaymentMode())->toBe(PaymentMode::ONLINE);
})->with('property');

it('can have co-borrowers, and can determine the youngest amongst', function (Property $property) {
    $borrower = (new Borrower($property))->setBirthdate(Carbon::parse('1999-03-17'));
    $co_borrower[0] = (new Borrower($property))->setBirthdate(Carbon::parse('2001-03-17'));
    $co_borrower[1] = (new Borrower($property))->setBirthdate(Carbon::parse('2000-03-17'));
    $borrower->addCoBorrower($co_borrower[0])->addCoBorrower($co_borrower[1]);
    $borrower->getCoBorrowers()->each(function ($value, $index) use ($co_borrower) {
        expect($value)->toBe($co_borrower[$index]);
    });
    expect($borrower->getOldestAmongst())->toBe($borrower);
})->with('property');

it('has gross monthly income, monthly disposable income and joint monthly disposable income', function (Borrower $borrower, Property $property) {
    $borrower->setProperty($property);
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
    expect($borrower->getJointMonthlyDisposableIncome()->inclusive()->getAmount()->toFloat())->toBe(4800.0 + 4480.0 + 4160.0);
})->with('borrower_with_co-borrowers', 'property');

it('has monthly income and disposable monthly income', function (Property $property) {
    $borrower = new Borrower($property);
    $borrower->setGrossMonthlyIncome($salary = Money::of(12000.0, 'PHP'));
    $borrower->addOtherSourcesOfIncome('commissions', $commissions = Money::of(2000.0, 'PHP'));
    expect($borrower->getGrossMonthlyIncome()->base()->compareTo($salary))->toBe(0);
    expect($borrower->getGrossMonthlyIncome()->inclusive()->compareTo($salary->plus($commissions)))->toBe(0);
    expect($borrower->getMonthlyDisposableIncome()
        ->compareTo($borrower->getGrossMonthlyIncome()->inclusive()->multipliedBy($property->getDisposableIncomeRequirementMultiplier())))
        ->toBe(0);
})->with('property');

it('has borrowing ages', function (Property $property) {
    $borrower = new Borrower($property);
    $borrower->setLendingInstitution(new LendingInstitution);
    $age = $borrower->getMinimumBorrowingAge();
    $birthdate = Carbon::now()->addYears(-1 * $age);
    $borrower = $borrower->setBirthdate($birthdate);
    expect((int) floor($borrower->getBirthdate()->diffInYears()))->toBe($age);

    $age = $borrower->getMaximumBorrowingAge();
    $birthdate = Carbon::now()->addYears(-1 * $age);
    $borrower = $borrower->setBirthdate($birthdate);
    expect((int) floor($borrower->getBirthdate()->diffInYears()))->toBe($age);
})->with('property');

it('has a legal age', function (Property $property) {
    $borrower = new Borrower($property);
    $borrower->setLendingInstitution(new LendingInstitution);
    $years = $borrower->getMinimumBorrowingAge() - 1;
    $birthdate = Carbon::today()->addYears(-$years);
    $borrower->setBirthdate($birthdate);
})->with('property')->expectException(MinimumBorrowingAgeNotMet::class);

it('has a retirement age', function (Property $property) {
    $borrower = new Borrower;
    $borrower->setLendingInstitution(new LendingInstitution);
    $years = $borrower->getMaximumBorrowingAge() + 1;
    $birthdate = Carbon::today()->addYears(-$years);
    $borrower->setBirthdate($birthdate);
})->with('property')->expectException(MaximumBorrowingAgeBreached::class);

it('has borrower data', function (Property $property) {
    $borrower = new Borrower($property);
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
        'payment_mode' => $borrower->getPaymentMode()->getName(),
        'maturity_date' => $borrower->getMaturityDate()->format('Y-m-d'),
        'age_at_maturity_date' => $borrower->getAgeAtMaturityDate(),
        'lending_institution_alias' => $borrower->getLendingInstitution()->getAlias(),
        'lending_institution_name' => $borrower->getLendingInstitution()->getName(),
        'maximum_term_allowed' => $borrower->getMaximumTermAllowed(),
        'repricing_frequency' => $borrower->getAffordabilityRates()->getRepricingFrequency(),
        'interest_rate' => $borrower->getAffordabilityRates()->getInterestRate()
    ]);
})->with('property');

it('has contact id', function (Property $property) {
    $borrower = new Borrower($property);
    expect($borrower->getContactId())->toBeUuid();
    $contact_id = 'ABC-123';
    $borrower->setContactId($contact_id);
    expect($borrower->getContactId())->toBe($contact_id);
})->with('property');

it('has a maturity date', function (Property $property) {
    $borrower = (new Borrower($property))->setBirthdate(Carbon::parse('1999-03-17'));
    $borrower->setMaturityDate(Carbon::parse('2029-03-17'));
    expect($borrower->getAgeAtMaturityDate())->toBe(30.0);
})->with('property');

it('has a landing institution', function (Property $property) {
    $borrower = new Borrower($property);
    $lending_institution = new LendingInstitution;
    $borrower->setLendingInstitution($lending_institution);
    expect($borrower->getLendingInstitution())->toBe($lending_institution);
})->with('property');

dataset('institution ages', function () {
    return [
        fn() => ['institution' => 'hdmf', 'age' => 25, 'maximum_paying_age' => 70, 'override_maximum_paying_age' => 70, 'off_set' =>  0, 'guess_max_term_allowed' => 30    ],
        fn() => ['institution' => 'hdmf', 'age' => 25, 'maximum_paying_age' => 70, 'override_maximum_paying_age' => 65, 'off_set' =>  0, 'guess_max_term_allowed' => 30    ],
        fn() => ['institution' => 'rcbc', 'age' => 25, 'maximum_paying_age' => 65, 'override_maximum_paying_age' => 65, 'off_set' => -1, 'guess_max_term_allowed' => 20    ],
        fn() => ['institution' => 'hdmf', 'age' => 40, 'maximum_paying_age' => 70, 'override_maximum_paying_age' => 70, 'off_set' =>  0, 'guess_max_term_allowed' => 30    ],
        fn() => ['institution' => 'hdmf', 'age' => 40, 'maximum_paying_age' => 70, 'override_maximum_paying_age' => 65, 'off_set' =>  0, 'guess_max_term_allowed' => 25    ],
        fn() => ['institution' => 'rcbc', 'age' => 40, 'maximum_paying_age' => 65, 'override_maximum_paying_age' => 65, 'off_set' => -1, 'guess_max_term_allowed' => 20    ],
        fn() => ['institution' => 'hdmf', 'age' => 45, 'maximum_paying_age' => 70, 'override_maximum_paying_age' => 70, 'off_set' =>  0, 'guess_max_term_allowed' => 25    ],
        fn() => ['institution' => 'hdmf', 'age' => 45, 'maximum_paying_age' => 70, 'override_maximum_paying_age' => 65, 'off_set' =>  0, 'guess_max_term_allowed' => 20    ],
        fn() => ['institution' => 'rcbc', 'age' => 45, 'maximum_paying_age' => 65, 'override_maximum_paying_age' => 65, 'off_set' => -1, 'guess_max_term_allowed' => 20 - 1],
        fn() => ['institution' => 'hdmf', 'age' => 50, 'maximum_paying_age' => 70, 'override_maximum_paying_age' => 70, 'off_set' =>  0, 'guess_max_term_allowed' => 20    ],
        fn() => ['institution' => 'hdmf', 'age' => 50, 'maximum_paying_age' => 70, 'override_maximum_paying_age' => 65, 'off_set' =>  0, 'guess_max_term_allowed' => 15    ],
        fn() => ['institution' => 'rcbc', 'age' => 50, 'maximum_paying_age' => 65, 'override_maximum_paying_age' => 65, 'off_set' => -1, 'guess_max_term_allowed' => 15 - 1],
        fn() => ['institution' => 'hdmf', 'age' => 55, 'maximum_paying_age' => 70, 'override_maximum_paying_age' => 70, 'off_set' =>  0, 'guess_max_term_allowed' => 15    ],
        fn() => ['institution' => 'hdmf', 'age' => 55, 'maximum_paying_age' => 70, 'override_maximum_paying_age' => 65, 'off_set' =>  0, 'guess_max_term_allowed' => 10    ],
        fn() => ['institution' => 'rcbc', 'age' => 55, 'maximum_paying_age' => 65, 'override_maximum_paying_age' => 65, 'off_set' => -1, 'guess_max_term_allowed' => 10 - 1],
        fn() => ['institution' => 'hdmf', 'age' => 60, 'maximum_paying_age' => 70, 'override_maximum_paying_age' => 70, 'off_set' =>  0, 'guess_max_term_allowed' => 10    ],
        fn() => ['institution' => 'hdmf', 'age' => 60, 'maximum_paying_age' => 70, 'override_maximum_paying_age' => 65, 'off_set' =>  0, 'guess_max_term_allowed' => 5     ],
        fn() => ['institution' => 'rcbc', 'age' => 60, 'maximum_paying_age' => 65, 'override_maximum_paying_age' => 65, 'off_set' => -1, 'guess_max_term_allowed' => 5 -  1],
    ];
});

it('has a maximum term allowed', function (Property $property, array $params) {
    $borrower = (new Borrower($property))
        ->setAge($params['age'])
        ->setLendingInstitution(new LendingInstitution($params['institution']));
    expect($borrower->getLendingInstitution()->getMaximumPayingAge())->toBe($params['maximum_paying_age']);
    $override_maximum_paying_age = ($borrower->getLendingInstitution()->getMaximumPayingAge() != $params['override_maximum_paying_age'])
        ? $params['override_maximum_paying_age']
        : null;
    expect($borrower->getLendingInstitution()->getOffset())->toBe($params['off_set']);
    expect($borrower->getMaximumTermAllowed($override_maximum_paying_age))->toBe($params['guess_max_term_allowed']);
})->with('property', 'institution ages');

it('implements buyer interface', function () {
    $borrower = (new Borrower)->setBirthdate(Carbon::parse('1999-03-17'))
        ->setGrossMonthlyIncome(Money::of(15000.0, 'PHP'));
    expect($borrower->getWages()->compareTo($borrower->getGrossMonthlyIncome()->inclusive()))->toBe(Amount::EQUAL);
    expect($borrower->getMobile()->equals('09173171999', 'PH'))->toBeTrue();
    expect($borrower->getSellerCommissionCode())->toBe('AA537');
    expect($borrower->getContactId())->toBeString();
});

it('throws error if birthdate is not set', function (){
    $borrower = (new Borrower)->setGrossMonthlyIncome(Money::of(15000.0, 'PHP'));
    expect($borrower->getMaximumTermAllowed())->toBeGreaterThan(25);
})->expectException(BirthdateNotSet::class);
