<?php

namespace Homeful\Borrower\Traits;

use Homeful\Borrower\Exceptions\MaximumBorrowingAgeBreached;
use Homeful\Borrower\Exceptions\MinimumBorrowingAgeNotMet;
use Homeful\Borrower\Borrower;
use Illuminate\Support\Carbon;
use DateTime;

trait HasDates
{
    /**
     * @param Carbon $value
     * @return HasDates|Borrower
     *
     * @throws MaximumBorrowingAgeBreached
     * @throws MinimumBorrowingAgeNotMet
     */
    public function setBirthdate(Carbon $value): self
    {
        if ((int) floor($value->diffInYears(Carbon::now())) < self::getMinimumBorrowingAge()) {
            throw new MinimumBorrowingAgeNotMet;
        }
        if ((int) floor($value->diffInYears(Carbon::now())) > self::getMaximumBorrowingAge()) {
            throw new MaximumBorrowingAgeBreached;
        }
        $this->birthdate = $value;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getBirthdate(): Carbon
    {
        return $this->birthdate;
    }

    /**
     * @param int $years
     * @return HasDates|Borrower
     *
     * @throws MaximumBorrowingAgeBreached
     * @throws MinimumBorrowingAgeNotMet
     */
    public function setAge(int $years): self
    {
        $birthdate = Carbon::now()->addYears(-1 * $years);
        $this->setBirthdate($birthdate);

        return $this;
    }

    /**
     * @return float
     */
    public function getAge(): float
    {
        return round($this->getBirthdate()->diffInYears(), 1, PHP_ROUND_HALF_UP);
    }

    /**
     * @return Borrower
     */
    public function getOldestAmongst(): Borrower
    {
        $oldest = $this;
        $this->co_borrowers->each(function (Borrower $co_borrower) use (&$oldest) {
            if ($co_borrower->getBirthdate()->lt($oldest->getBirthdate())) {
                $oldest = $co_borrower;
            }
        });

        return $oldest;
    }

    /**
     * @param DateTime|null $reference
     * @return string
     */
    public function getFormattedAge(DateTime $reference = null): string
    {
        return formatted_age($this->getBirthdate(), $reference);
    }

    /**
     * @param Carbon $value
     * @return HasDates|Borrower
     */
    public function setMaturityDate(Carbon $value): self
    {
        $this->maturity_date = $value;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getMaturityDate(): Carbon
    {
        return $this->maturity_date ?? now();
    }

    public function getAgeAtMaturityDate(): float
    {
        return round($this->getBirthdate()->diffInYears($this->getMaturityDate()), 1, PHP_ROUND_HALF_UP);
    }

    /**
     * @return int
     */
    public function getMaximumTermAllowed(): int
    {
        return $this->getLendingInstitution()->getMaximumTermAllowed($this->getBirthdate());
    }
}
