<?php

namespace Core\Exceptions;

class SlimModuleValidatorException extends \Exception
{
    public function __construct($message = "", $code = 412, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
