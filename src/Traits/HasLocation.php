<?php

namespace Homeful\Borrower\Traits;

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
}
