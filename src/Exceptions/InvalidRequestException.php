<?php

namespace Core\Exceptions;

use Exception;

class InvalidRequestException extends Exception
{
    public function __construct(string $message = 'Requisição inválida.', int $code = 400)
    {
        parent::__construct($message, $code);
    }
} 