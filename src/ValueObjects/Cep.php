<?php

namespace Core\ValueObjects;

use Core\Utils\Mask;
use Core\Utils\Str;

class Cep extends ValueObject
{

    protected function sanitize($value)
    {
        return Str::numbers($value);
    }

    public function format(): string
    {
        return Mask::cep($this->value);

    }

}