<?php

namespace Core\Utils;


use Core\Contracts\IValidate;
use Core\Exceptions\ValidatorException;
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
        {
            $validation = (new \Awurth\SlimValidation\Validator())->value($value, $rules, $key, $group, $messages);
            if (!$validation->isValid()) {
                throw new ValidatorException('Erro na validação dos dados,', $validation->getErrors());
            }
        }
    }

    /**
     * @param array $data
     * @param array $rules
     * @param string|null $group
     * @param array $messages
     * @param $default
     */
    public
    static function request(array $data, array $rules, string $group = null, array $messages = [], $default = null): void
    {
        $messages = array_merge(self::defaultMessages(), $messages);
        $validation = (new \Awurth\SlimValidation\Validator())->array($data, $rules, $group, $messages, $default);
        if (!$validation->isValid()) {
            throw new ValidatorException('Erro na validação dos dados,', $validation->getErrors());
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

    private static function defaultMessages()
    {
        return [
            'notEmpty' => 'O campo {{name}} é obrigatorio',
            'notOptional' => 'O campo {{name}} é opcional',
            'intType' => 'O campo {{name}} deve conter um  numero inteiro',
            'intVal' => 'O campo {{name}} deve ser um numero inteiro',
            'booType' => 'O campo {{name}} deve ser um booleano',
            'email' => 'O campo {{name}} informado é invalido',
        ];
    }
}