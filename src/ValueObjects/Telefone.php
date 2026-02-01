<?php
declare(strict_types=1);

namespace Core\ValueObjects;

use Core\Traits\Mask;
use Core\Traits\Str;

class Telefone extends ValueObject
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
        return Mask::phone($this->value);
    }
}