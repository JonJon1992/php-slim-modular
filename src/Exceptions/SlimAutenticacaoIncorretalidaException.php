<?php

namespace Core\Exceptions;

use Throwable;

class SlimAutenticacaoIncorretalidaException extends \Exception
{
    public function __construct($message = "Autenticação incorreta.", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

