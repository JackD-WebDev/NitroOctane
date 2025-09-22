<?php

namespace App\Exceptions;

use Exception;

class ValidationErrorException extends Exception
{
    public function __construct($message = "Validation failed", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}