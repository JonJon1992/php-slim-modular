<?php

namespace Core\Exceptions\Exception;

use Exception;

class AppException extends Exception
{
    public function __construct(string $message = 'Erro na aplicação.', int $code = 400, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}