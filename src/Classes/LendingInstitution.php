<?php

namespace Homeful\Borrower\Classes;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class LendingInstitution
{
    protected string $key;

    /**
     * @throws \Exception
     */
    public function __construct(string $key = 'hdmf')
    {
        if (!in_array($key, self::keys()))
            throw new \Exception('invalid key');

        $this->key = $key;
    }

    public static function keys(): array
    {
        $array = config('borrower.lending_institutions');

        return array_keys($array);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getRecord(): array
    {
        $key = $this->getKey();

        return config("borrower.lending_institutions.$key");
    }

    public function getAttribute(string $field): mixed
    {
        return Arr::get($this->getRecord(), $field);
    }
    public function getName(): string
    {
        return $this->getAttribute('name');
    }

    public function getAlias(): string
    {
        return $this->getAttribute('alias');
    }

    public function getMinimumBorrowingAge(): int
    {
        return $this->getAttribute('borrowing_age.minimum');
    }

    public function getMaximumBorrowingAge(): int
    {
        return $this->getAttribute('borrowing_age.maximum');
    }

    public function getOffset(): int
    {
        return $this->getAttribute('borrowing_age.offset');
    }

    public function getMaximumTerm(): int
    {
        return $this->getAttribute('maximum_term');
    }

    public function getMaximumPayingAge(): int
    {
        return $this->getAttribute('maximum_paying_age');
    }

    public function getMaximumTermAllowed(Carbon $birthdate, ?int $override_maximum_paying_age = null): int
    {
        $age = round($birthdate->diffInYears(), 1);
        $maximum_paying_age = $override_maximum_paying_age ?: $this->getMaximumPayingAge();

        return min(($maximum_paying_age + $this->getOffset() - $age), $this->getMaximumTerm());
    }
}
