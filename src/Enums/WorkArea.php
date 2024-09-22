<?php

namespace Homeful\Borrower\Enums;

enum WorkArea
{
    case HUC;

    case REGION;

    public function getName(): string
    {
        return match ($this) {
            self::HUC => 'HUC',
            self::REGION => 'Region'
        };
    }

    public static function fromRegional(bool $value): self
    {
        return $value ? self::REGION : self::HUC;
    }
}
