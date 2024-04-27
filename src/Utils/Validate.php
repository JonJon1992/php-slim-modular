<?php

namespace Core\Utils;

use Awurth\Validator\Validator as SlimValidator;
use Core\Contracts\IValidate;
use Core\Exceptions\SlimValidatorException;
use Respect\Validation\Validator;

class Validate implements IValidate
{


    /**
     * @param $value
     * @param array $rules
     * @param string $key
     * @param string|null $group
     * @param array $messages
     */
    public static function value($value, array $rules, string $key, string $group = null, array $messages = []): void
    {
        self::validate($value, $rules, $messages);
    }

    /**
     * @param array $data
     * @param array $values
     * @param string|null $group
     * @param array $messages
     * @param $default
     */
    public static function request(array $data, array $values, string $group = null, array $messages = [], $default = null): void
    {
        self::validate($data, $values, $messages);
    }

    /**
     * @param  $subject
     * @param  $rules
     * @param  $messages
     */
    private static function validate($subject, $rules, $messages): void
    {
        $validation = SlimValidator::create();
        $errors = $validation->validate($subject, $rules, $messages);

        if ($errors->has(0)) {
            throw new SlimValidatorException($errors->get(0)->getMessage(), 412);
        }
    }

    /**
     * @param string $ruleName
     * @param array $arguments
     * @return Validator
     */
    public static function __callStatic(string $ruleName, array $arguments): Validator
    {
        return Validator::create()->__call($ruleName, $arguments);
    }

}