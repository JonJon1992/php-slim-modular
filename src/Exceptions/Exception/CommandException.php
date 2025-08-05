<?php

namespace Core\Exceptions\Exception;

use Exception;

class CommandException extends Exception
{
    public function __construct(string $message = 'Erro no comando.', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 