<?php
declare(strict_types=1);

namespace Core\Exceptions\Exception;

use Throwable;

class SlimAutenticacaoIncorretalidaException extends \Exception
{
    public function __construct(string $message = "Autenticação incorreta.", int $code = 401, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

