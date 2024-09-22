<?php

namespace Homeful\Borrower\Enums;

enum EmploymentType
{
    case LOCAL_GOVERNMENT;
    case LOCAL_PRIVATE;
    case OFW;
    case BUSINESS;

    public function getName(): string
    {
        return match ($this) {
            self::LOCAL_GOVERNMENT => 'Government',
            self::LOCAL_PRIVATE => 'Private',
            self::OFW => 'OFW',
            self::BUSINESS => 'Business'
        };
    }
}
