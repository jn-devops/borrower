<?php
declare(strict_types=1);

namespace Homeful\Borrower\Data;

use Homeful\Borrower\Borrower;
use Spatie\LaravelData\Data;

class BorrowerData extends Data
{
    public function __construct(
        public float $gross_monthly_income,
        public bool $regional,
        public string $birthdate
    ) {}

    public static function fromObject(Borrower $borrower): self
    {
        return new self(
            gross_monthly_income: $borrower->getGrossMonthlyIncome()->inclusive()->getAmount()->toFloat(),
            regional: $borrower->getRegional(),
            birthdate: $borrower->getBirthdate()->format('Y-m-d'),
        );
    }
}
