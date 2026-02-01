<?php
declare(strict_types=1);

namespace Core\ValueObjects;

use Core\Traits\Mask;
use Core\Traits\Str;

class Cnpj extends ValueObject
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
        return Mask::cnpj($this->value);
    }
}