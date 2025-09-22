<?php

namespace App\Exceptions;

use Exception;

class ModelNotDefined extends Exception
{
    public function __construct($message = "Model is not defined", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}