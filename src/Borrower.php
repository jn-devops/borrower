<?php

namespace Homeful\Borrower;

use Brick\Money\Money;
use DateTime;
use Homeful\Borrower\Traits\{HasCoBorrowers, HasDates, HasDeprecated, HasLocation, HasNumbers};
use Homeful\Borrower\Classes\AffordabilityRates;
use Homeful\Borrower\Enums\EmploymentType;
use Homeful\Borrower\Enums\PaymentMode;
use Homeful\Common\Enums\WorkArea;
use Homeful\Property\Property;
use Illuminate\Support\Collection;
use Illuminate\Support\{Arr, Str};
use Illuminate\Support\Carbon;
use Whitecube\Price\Price;


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
 */
class Borrower
{
    use HasCoBorrowers;
    use HasDeprecated;
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

    /**
     * @return $this
     */
    public function setContactId(string $contact_id): self
    {
        $this->contact_id = $contact_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getContactId(): string
    {
        return $this->contact_id ?? Str::uuid();
    }

    public function setPaymentMode(PaymentMode $mode): self
    {
        $this->payment_mode = $mode;

        return $this;
    }

    public function getPaymentMode(): PaymentMode
    {
        return $this->payment_mode ?? PaymentMode::ONLINE;
    }

    /**
     * @return int
     */
    static public function getMinimumBorrowingAge(): int
    {
        return config('borrower.borrowing_age.minimum');
    }

    /**
     * TODO: change this, don't use arrays e.g., see getAffordabilityRates()
     * @param string $lending_institution
     * @return int
     */
    static public function getMaximumBorrowingAge(string $lending_institution = 'default'): int
    {
        return Arr::get(config('borrower.borrowing_age.maximum'), $lending_institution, config('borrower.borrowing_age.maximum.default'));
    }
}
