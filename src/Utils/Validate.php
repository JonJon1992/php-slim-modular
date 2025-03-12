<?php

namespace Core\Utils;

use Core\Contracts\IValidate;
use Core\Exceptions\ValidatorException;
use Awurth\Validator\Validator as AwurthValidator;
use Respect\Validation\Validator;

class Validate implements IValidate
{

    /**
     * @param $value
     * @param array $rules
     * @param string $key
     * @param string|null $group
     * @param array $messages
     * @throws ValidatorException
     */
    public static function value($value, array $rules, string $key, string $group = null, array $messages = []): void
    {
        self::validate($value, $rules, $messages);
    }

    /**
     * @param array $data
     * @param array $rules
     * @param string|null $group
     * @param array $messages
     * @param null $default
     * @throws ValidatorException
     */
    public
    static function request(array $data, array $rules, string $group = null, array $messages = [], $default = null): void
    {
        self::validate($data, $rules, $messages);
    }

    /**
     * @param $subject
     * @param array $rules
     * @param array $messages
     * @return void
     * @throws ValidatorException
     */
    private static function validate($subject, array $rules, array $messages = []): void
    {
        $validation = AwurthValidator::create();
        $errors = $validation->validate($subject, $rules, $messages);

        if ($errors->has(0)) {
            throw new ValidatorException($errors->get(0)->getMessage(), 412);
        }

    }

    /**
     * @param string $ruleName
     * @param array $arguments
     * @return Validator
     */
    public
    static function __callStatic(string $ruleName, array $arguments): Validator
    {
        return Validator::create()->__call($ruleName, $arguments);
    }

}