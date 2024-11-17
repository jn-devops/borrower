<?php

namespace Homeful\Borrower\Exceptions;

use Exception;

class MaximumBorrowingAgeBreached extends Exception {
    protected $message = 'Maximum borrowing age breached!';
}
