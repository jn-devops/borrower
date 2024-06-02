<?php

namespace Homeful\Borrower\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Homeful\Borrower\Borrower
 */
class Borrower extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Homeful\Borrower\Borrower::class;
    }
}
