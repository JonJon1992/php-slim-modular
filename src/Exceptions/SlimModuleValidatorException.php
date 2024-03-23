<?php

namespace Core\Exceptions;

use Exception;
use Throwable;

class SlimValidatorException extends Exception
{
    public function __construct($message = "", $code = 412, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
