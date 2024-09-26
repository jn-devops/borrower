<?php

declare(strict_types=1);

namespace Homeful\Borrower\Data;

use Homeful\Borrower\Borrower;
use Spatie\LaravelData\Data;
use Carbon\Carbon;

class BorrowerData extends Data
{
    public function __construct(
        public float $gross_monthly_income,
        public bool $regional,
        public string $birthdate,
        public float $age,
        public string $as_of_date,
        public string $work_area,
        public string $employment_type,
        public string $formatted_age,
        public string $payment_mode,
        public string $maturity_date,
        public float $age_at_maturity_date,
        public string $lending_institution_alias,
        public string $lending_institution_name,
    ) {}

    public static function fromObject(Borrower $borrower): self
    {
        return new self(
            gross_monthly_income: $borrower->getGrossMonthlyIncome()->inclusive()->getAmount()->toFloat(),
            regional: $borrower->getRegional(),
            birthdate: $borrower->getBirthdate()->format('Y-m-d'),
            age: $borrower->getAge(),
            as_of_date: Carbon::today()->format('Y-m-d'),
            work_area: $borrower->getWorkArea()->getName(),
            employment_type: $borrower->getEmploymentType()->getName(),
            formatted_age: $borrower->getFormattedAge(),
            payment_mode: $borrower->getPaymentMode()->getName(),
            maturity_date: $borrower->getMaturityDate()->format('Y-m-d'),
            age_at_maturity_date: $borrower->getAgeAtMaturityDate(),
            lending_institution_alias: $borrower->getLendingInstitution()->getAlias(),
            lending_institution_name: $borrower->getLendingInstitution()->getName()
        );
    }
}
