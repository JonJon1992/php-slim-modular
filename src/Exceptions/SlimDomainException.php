<?php

namespace Core\Exceptions;

use Throwable;

class SlimDomainException extends \Exception
{
    public function __construct($message = "", $code = 400,   Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
