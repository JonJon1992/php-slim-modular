<?php

namespace Core\ValueObjects;

use Core\Utils\Mask;
use Core\Utils\Str;

class Cep extends ValueObject
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
        return Mask::cep($this->value);

    }

}