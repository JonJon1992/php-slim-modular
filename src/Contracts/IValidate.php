<?php

namespace Core\Contracts;
interface IValidate
{
    public static function value($value, array $rules, string $key, string $group = null, array $messages = []);
    public static function request(array $data, array $rules, string $group = null, array $messages = [], $default = null);

}