<?php
declare(strict_types=1);

namespace Core\ValueObjects;

use Core\Utils\Mask;
use Core\Utils\Str;

class Cpf extends ValueObject
{
    /**
     * @param mixed $value
     */
    protected function sanitize($value): string
    {
        return Str::numbers((string)$value);
    }

    /**
     * @return string
     */
    public function format(): string
    {
        return Mask::cpf($this->value);
    }
}