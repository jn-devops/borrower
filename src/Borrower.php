<?php

namespace Homeful\Borrower;

use Homeful\Borrower\Traits\{HasCoBorrowers, HasDates, HasDeprecated, HasLocation, HasNumbers};
use Illuminate\Support\Collection;
use Illuminate\Support\{Arr, Str};
use Illuminate\Support\Carbon;
use Whitecube\Price\Price;

class Borrower
{
    use HasCoBorrowers;
    use HasDeprecated;
    use HasLocation;
    use HasNumbers;
    use HasDates;

    protected Price $gross_monthly_income;

    protected bool $regional = false;

    protected Collection $co_borrowers;

    protected Carbon $birthdate;

    public int $maximum_age_at_loan_maturity = 70; //years old

    public string $contact_id;

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

    /**
     * @return int
     */
    static public function getMinimumBorrowingAge(): int
    {
        return config('borrower.borrowing_age.minimum');
    }

    /**
     * @param string $lending_institution
     * @return int
     */
    static public function getMaximumBorrowingAge(string $lending_institution = 'default'): int
    {
        return Arr::get(config('borrower.borrowing_age.maximum'), $lending_institution, config('borrower.borrowing_age.maximum.default'));
    }
}
