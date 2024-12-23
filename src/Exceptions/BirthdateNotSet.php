<?php

namespace Homeful\Borrower\Exceptions;

use Exception;

class BirthdateNotSet extends Exception {
    protected $message = 'Birthdate has not been set!';
}
