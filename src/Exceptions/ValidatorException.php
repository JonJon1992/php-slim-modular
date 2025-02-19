<?php

namespace Core\Exceptions;

use Exception;
use Throwable;

class ValidatorException extends Exception
{
    private $errors;

    public function __construct(string $message = "", array $errors = [], int $code = 412, Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors()
    {
        return $this->errors;
    }

}