<?php

namespace Core\Exceptions\Exception;

use Exception;

class AuthenticationException extends Exception
{
    public function __construct(string $message = 'Usuário não autenticado.', int $code = 401)
    {
        parent::__construct($message, $code);
    }
} 