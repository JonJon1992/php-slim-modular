<?php

namespace Core\ValueObjects;

use Core\Utils\Mask;
use Core\Utils\Str;

class Telefone extends ValueObject
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
        return Mask::phone($this->value);
    }
}