<?php

namespace Homeful\Borrower;

use Homeful\Borrower\Traits\{HasCoBorrowers, HasDates, HasDeprecated, HasLocation, HasNumbers, HasProperties};
use Homeful\Borrower\Classes\{AffordabilityRates, LendingInstitution};
use Homeful\Borrower\Enums\{EmploymentType, PaymentMode};
use Homeful\Common\Interfaces\BorrowerInterface;
use Propaganistas\LaravelPhone\PhoneNumber;
use Homeful\Common\Enums\WorkArea;
use Illuminate\Support\Collection;
use Homeful\Property\Property;
use Illuminate\Support\Carbon;
use Whitecube\Price\Price;
use Brick\Money\Money;
use DateTime;

/**
 * Class Borrower
 *
 * @property Price $gross_monthly_income
 * @property Collection $co_borrowers
 * @property Carbon $birthdate
 * @property string $contact_id
 * @property PaymentMode $payment_mode
 * @property bool $regional
 * @property EmploymentType $employment_type
 * @property Carbon $maturity_date
 * @property LendingInstitution $lending_institution
 *
 * @method Borrower addCoBorrower(Borrower $co_borrower)
 * @method Collection getCoBorrowers()
 * @method Price getJointMonthlyDisposableIncome()
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
 * @method Price getMonthlyDisposableIncome()
 * @method AffordabilityRates getAffordabilityRates()
 * @method Borrower setPaymentMode(PaymentMode $mode)
 * @method PaymentMode getPaymentMode()
 * @method Borrower setMaturityDate(Carbon $value)
 * @method Carbon getMaturityDate()
 * @method float getAgeAtMaturityDate()
 * @method Borrower setLendingInstitution(LendingInstitution $institution)
 * @method LendingInstitution getLendingInstitution()
 * @method int getMinimumBorrowingAge()
 * @method int getMaximumBorrowingAge()
 * @method int getMaximumTermAllowed()
 * @method Borrower setOverrideMaximumPayingAge(?int $override_maximum_paying_age)
 * @method int getOverrideMaximumPayingAge()
 * @method float getDisposableIncomeMultiplier()
 */
class Borrower implements BorrowerInterface
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

    protected Carbon $maturity_date;

    protected LendingInstitution $lending_institution;

    protected Property $property;

    protected float $disposable_income_multiplier;

    public function __construct(Property $property = null)
    {
        $this->co_borrowers = new Collection;
        if ($property)
            $this->property = $property;
    }

    public function setProperty(Property $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function getProperty(): ?Property
    {
        return $this->property ?? null;
    }

    /**
     * @deprecated
     */
    public function getWages(): Money|float
    {
        return $this->getGrossMonthlyIncome()->inclusive();
    }

    /**
     * @deprecated
     */
    public function getMobile(): PhoneNumber
    {
        return phone('09173171999', 'PH');
    }


    /**
     * @deprecated
     */
    public function getSellerCommissionCode(): string
    {
        return config('borrower.default_seller_code');
    }
}
