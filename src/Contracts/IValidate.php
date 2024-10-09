<?php

namespace Core\Contracts;

use Respect\Validation\StaticValidator;

/**
 * @mixin StaticValidator
 */
interface IValidate
{
    /**
     * @param $value
     * @param array $rules
     * @param string $key
     * @param string|null $group
     * @param array $messages
     */
    public static function value($value, array $rules, string $key, string $group = null, array $messages = []);

    /**
     * @param array $data
     * @param array $values
     * @param string|null $group
     * @param array $messages
     * @param $default
     */
    public static function request(array $data, array $values, string $group = null, array $messages = [], $default = null);

}