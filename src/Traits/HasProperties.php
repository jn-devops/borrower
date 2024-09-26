<?php

namespace Homeful\Borrower\Traits;

use Homeful\Borrower\Borrower;
use Homeful\Borrower\Enums\PaymentMode;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasProperties
{
    /**
     * @param string $contact_id
     * @return Borrower|HasProperties
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
