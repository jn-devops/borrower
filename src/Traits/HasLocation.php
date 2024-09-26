<?php

namespace Homeful\Borrower\Traits;

use Homeful\Borrower\Enums\EmploymentType;
use Homeful\Common\Enums\WorkArea;
use Homeful\Borrower\Borrower;

trait HasLocation
{
    /**
     * @param bool $value
     * @return Borrower|HasLocation
     */
    public function setRegional(bool $value): self
    {
        $this->regional = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function getRegional(): bool
    {
        return $this->regional ?? config('borrower.default_regional');
    }

    /**
     * @return WorkArea
     */
    public function getWorkArea(): WorkArea
    {
        return WorkArea::fromRegional($this->getRegional());
    }

    /**
     * @param WorkArea $area
     * @return Borrower|HasLocation
     */
    public function setWorkArea(WorkArea $area): self
    {
        $this->regional = $area == WorkArea::REGION;

        return $this;
    }

    /**
     * @param EmploymentType $type
     * @return Borrower|HasLocation
     */
    public function setEmploymentType(EmploymentType $type): self
    {
        $this->employment_type = $type;

        return $this;
    }

    /**
     * @return EmploymentType
     */
    public function getEmploymentType(): EmploymentType
    {
        return $this->employment_type ?? EmploymentType::LOCAL_PRIVATE;
    }
}
