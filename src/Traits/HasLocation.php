<?php

namespace Homeful\Borrower\Traits;

use Homeful\Borrower\Enums\EmploymentType;
use Homeful\Borrower\Enums\WorkArea;
use Homeful\Borrower\Borrower;

trait HasLocation
{
    protected bool $regional = false;
    protected EmploymentType $employment_type;

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

    public function getWorkArea(): WorkArea
    {
        return WorkArea::fromRegional($this->getRegional());
    }

    public function setWorkArea(WorkArea $area): self
    {
        $this->regional = $area == WorkArea::REGION;

        return $this;
    }

    public function setEmploymentType(EmploymentType $type): self
    {
        $this->employment_type = $type;

        return $this;
    }

    public function getEmploymentType(): EmploymentType
    {
        return $this->employment_type ?? EmploymentType::LOCAL_PRIVATE;
    }
}
