<?php

namespace Homeful\Borrower\Exceptions;

use Exception;

class MinimumBorrowingAgeNotMet extends Exception {
    protected $message = 'Minimum borrowing age breached!';
}
