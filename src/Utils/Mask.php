<?php

namespace Core\Utils;

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
}