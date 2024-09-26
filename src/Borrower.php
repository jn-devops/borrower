<?php

namespace Homeful\Borrower;

use Homeful\Borrower\Traits\{HasCoBorrowers, HasDates, HasDeprecated, HasLocation, HasNumbers, HasProperties};
use Homeful\Borrower\Classes\AffordabilityRates;
use Homeful\Borrower\Enums\EmploymentType;
use Homeful\Borrower\Enums\PaymentMode;
use Homeful\Common\Enums\WorkArea;
use Illuminate\Support\Collection;
use Homeful\Property\Property;
use Illuminate\Support\Carbon;
use Whitecube\Price\Price;
use Brick\Money\Money;
use DateTime;

/**
 * Class Property
 *
 * @property Price $gross_monthly_income
 * @property Collection $co_borrowers
 * @property Carbon $birthdate
 * @property string $contact_id
 * @property PaymentMode $payment_mode
 * @property bool $regional
 * @property EmploymentType $employment_type
 *
 * @method Borrower addCoBorrower(Borrower $co_borrower)
 * @method Collection getCoBorrowers()
 * @method Price getJointMonthlyDisposableIncome(Property $property)
 * @method Borrower setRegional(bool $value)
 * @method bool getRegional()
 * @method Borrower setWorkArea(WorkArea $area)()
 * @method WorkArea getWorkArea()
 * @method Borrower setEmploymentType(EmploymentType $type)
 * @method EmploymentType getEmploymentType()
 * @method Borrower setBirthdate(Carbon $value)
 * @method Carbon getBirthdate()
 * @method Borrower setAge(int $years)
 * @method float getAge()
 * @method Borrower getOldestAmongst()
 * @method string getFormattedAge(DateTime $reference = null)
 * @method Borrower setGrossMonthlyIncome(Price|Money|float $value)
 * @method Price getGrossMonthlyIncome()
 * @method Borrower addOtherSourcesOfIncome(string $name, Money|float $value)
 * @method Price getMonthlyDisposableIncome(Property $property)
 * @method AffordabilityRates getAffordabilityRates()
 * @method Borrower setPaymentMode(PaymentMode $mode)
 * @method PaymentMode getPaymentMode()
 */
class Borrower
{
    use HasCoBorrowers;
    use HasDeprecated;
    use HasProperties;
    use HasLocation;
    use HasNumbers;
    use HasDates;

    protected Price $gross_monthly_income;

    protected Collection $co_borrowers;

    protected Carbon $birthdate;

    public int $maximum_age_at_loan_maturity = 70; //years old

    public string $contact_id;

    public PaymentMode $payment_mode;

    protected bool $regional = false;

    protected EmploymentType $employment_type;

    public function __construct()
    {
        $this->co_borrowers = new Collection;
    }
}
