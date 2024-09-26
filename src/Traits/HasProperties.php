<?php

namespace Homeful\Borrower\Traits;

use Homeful\Borrower\Classes\LendingInstitution;
use Homeful\Borrower\Enums\PaymentMode;
use Homeful\Borrower\Borrower;
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
    public function getMinimumBorrowingAge(): int
    {
        return $this->getLendingInstitution()->getMinimumBorrowingAge();
    }

    /**
     * @return int
     */
    public function getMaximumBorrowingAge(): int
    {
        return $this->getLendingInstitution()->getMaximumBorrowingAge();
    }

    /**
     * @param LendingInstitution $institution
     * @return Borrower|HasProperties
     */
    public function setLendingInstitution(LendingInstitution $institution): self
    {
        $this->lending_institution = $institution;

        return $this;
    }

    /**
     * @return LendingInstitution
     */
    public function getLendingInstitution(): LendingInstitution
    {
        return $this->lending_institution ?? new LendingInstitution;
    }
}
