<?php

namespace Core\ValueObjects;

use Awurth\SlimValidation\Validator;
use Stringable;

abstract class ValueObject extends Stringable implements IValueObject
{

    protected $value;

    protected array $roles = [];

    public function __construct($value)
    {
        $value = $this->sanitize($value);
        $validations = (new Validator())->value($value, $this->roles(), 'value');
        if ($validations->isValid()) {
            throw  new \Exception($validations->getFirstError('value'));
        }
        $this->value = $value;
    }

    protected function sanitize($value)
    {
        return $value;
    }

    /**
     * @return string
     */
    public function format(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    protected function roles(): array
    {
        return $this->roles;
    }
}