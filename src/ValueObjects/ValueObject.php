<?php

namespace Core\ValueObjects;

use Core\Utils\Validate;
use Stringable;

abstract class ValueObject implements Stringable, IValueObject
{
    /**
     * @var mixed
     */
    protected $value;

    protected array $roles = [];

    public function __construct($value)
    {
        $value = $this->sanitize($value);
        try {
            Validate::value($value, $this->roles(), 'value');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        $this->value = $value;
    }

    /**
     * @param $value
     */
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

    /**
     * @return array
     */
    protected function roles(): array
    {
        return $this->roles;
    }
}