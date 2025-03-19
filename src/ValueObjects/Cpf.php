<?php

namespace Core\ValueObjects;

use Core\Utils\Mask;
use Core\Utils\Str;

class Cpf extends ValueObject
{
    /**
     * @param $value
     */
    protected function sanitize($value)
    {
        return Str::numbers($value);
    }

    /**
     * @return string
     */
    public function format(): string
    {
        return Mask::cpf($this->value);
    }
}