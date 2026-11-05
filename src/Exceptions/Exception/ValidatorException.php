<?php

namespace Core\Exceptions\Exception;

use Exception;
use Throwable;

class ValidatorException extends Exception
{

    public function __construct(string $message = "", int $code = 412, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


}