<?php

namespace Core\Utils;

use Awurth\Validator\Validator;
use Core\Contracts\IValidate;
use Core\Exceptions\SlimModuleValidatorException;

class Validate implements IValidate
{


    /**
     * @param $value
     * @param array $rules
     * @param string $key
     * @param string|null $group
     * @param array $messages
     */
    public static function value($value, array $rules, string $key, string $group = null, array $messages = [])
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
    public static function request(array $data, array $values, string $group = null, array $messages = [], $default = null)
    {
        self::validate($data, $values, $messages);
    }

    /**
     * @param  $subject
     * @param  $rules
     * @param  $messages
     */
    private static function validate($subject, $rules, $messages)
    {
        $validation = Validator::create();
        $errors = $validation->validate($subject, $rules, $messages);

        if ($errors->has(0)) {
            throw new SlimModuleValidatorException($errors->get(0)->getMessage(), 412);
        }
    }

}