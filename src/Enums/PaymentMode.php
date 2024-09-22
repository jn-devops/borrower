<?php

namespace Homeful\Borrower\Enums;

enum PaymentMode
{
    case OVER_THE_COUNTER;
    case SALARY_DEDUCTION;
    case ONLINE;

    public function getName(): string
    {
        return match ($this) {
            self::OVER_THE_COUNTER => 'Over-The-Counter',
            self::SALARY_DEDUCTION => 'Salary Deduction',
            self::ONLINE => 'Online'
        };
    }
}
