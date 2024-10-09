<?php

namespace Core\Utils;

class Scrambler
{
    /**
     * @param string $str
     * @return string
     */
    public static function encode(string $str): string
    {
        return strtr(base64_encode(implode('', array_map(function ($chr) {
            return chr(($rand = rand(33, 126))) . chr((((ord($chr) - 33) + $rand) % 94) + 33);
        }, str_split(base64_encode($str))))), '+/=', '._-');
    }

    /**
     * @param string $str
     * @return string
     */
    public static function decode(string $str): string
    {
        return base64_decode(implode('', array_map(function ($chr) {
            $chr = str_split($chr);
            return chr((($rand = (ord($chr[1]) - 33) - (ord($chr[0]) - (94 * ((int)(ord($chr[0]) / 94))))) < 0 ? 94 + $rand : $rand) + 33);
        }, str_split(base64_decode(strtr($str, '._-', '+/=')), 2))));
    }
}