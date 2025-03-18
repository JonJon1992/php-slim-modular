<?php

namespace Core\Utils;
enum TypeMask: string
{
    case CPF = 'cpf';
    case TELEFONE = 'telefone';
    case EMAIL = 'email';
}

class Mask
{

    public static function to($mask, $value)
    {
        $value = str_replace(" ", "", $value);

        for ($i = 0; $i < strlen($value); $i++) {
            $mask[strpos($mask, "#")] = $value[$i];
        }

        return $mask;
    }

    public static function cpf($value)
    {
        return self::to('###.###.###-##', $value);
    }

    public static function cnpj($value)
    {
        return self::to('##.###.###/####-##', $value);
    }

    public static function cep($value)
    {
        return self::to('#####-###', $value);
    }

    public static function date($value)
    {
        return self::to('##/##/####', $value);
    }

    public static function phone($value)
    {
        $size = strlen($value);

        if ($size == 7) {
            return self::to('###-####', $value);
        }

        if ($size == 8) {
            return self::to('####-####', $value);
        }

        if ($size == 9) {
            return self::to('(##) ###-####', $value);
        }

        if ($size == 10) {
            return self::to('(##) ####-####', $value);
        }

        if ($size == 11) {
            return self::to('(##) #####-####', $value);
        }

        return $value;
    }

    public static function maskSensitive($type, $value)
    {
        return match ($type) {
            TypeMask::CPF => (strlen($value) === 11) ? substr($value, 0, 3) . ".***.***-" . substr($value, -2) : 'CPF inválido.',
            TypeMask::TELEFONE => match (strlen($value)) {
                11 => "(**) *****-" . substr($value, -4),
                10 => "(**) ****-" . substr($value, -4),
                default => 'Telefone inválido.',
            },
            TypeMask::EMAIL => preg_replace('/(^.{2}).*@/', '$1*****@', $value),
            default => 'Formato invalido.',
        };


    }

}