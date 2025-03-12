<?php

namespace Core\Exceptions;

use Exception;
use Throwable;

class ValidatorException extends Exception
{

    public function __construct(string $message = "", int $code = 412, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


}