<?php

namespace Core\Utils;

use Respect\Validation\Validator as RespectValidator;
use Core\Contracts\IValidate;
use Core\Exceptions\Exception\ValidatorException;
use Respect\Validation\Exceptions\ComponentException;
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
        // Para Value Objects, vamos usar uma abordagem mais simples
        // já que os rules são passados como array de regras do Respect\Validation
        if (isset($rules['rules'])) {
            $validator = $rules['rules'];
            if (!$validator->validate($subject)) {
                throw new ValidatorException($validator->getMessage(), 412);
            }
        } else {
            // Fallback para validação simples
            foreach ($rules as $rule) {
                if (!$rule->validate($subject)) {
                    throw new ValidatorException($rule->getMessage(), 412);
                }
            }
        }
    }

    /**
     * Creates a new Validator instance with a rule that was called on the static method.
     *
     * @param mixed[] $arguments
     *
     * @throws ComponentException
     */
    public
    static function __callStatic(string $ruleName, array $arguments): Validator
    {
        return Validator::create()->__call($ruleName, $arguments);
    }

}