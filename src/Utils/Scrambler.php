<?php

declare(strict_types=1);

namespace Core\Utils;

class Scrambler
{
    /**
     * @param string $str
     * @return string
     */
    public static function encode(string $str): string
    {
        $encoded = base64_encode($str);
        $scrambled = implode('', array_map(function ($chr) {
            $rand = random_int(33, 126);
            $offset = ((ord($chr) - 33) + $rand) % 94;
            return chr($rand) . chr($offset + 33);
        }, str_split($encoded)));

        return strtr(base64_encode($scrambled), '+/=', '._-');
    }

    /**
     * @param string $str
     * @return string
     */
    public static function decode(string $str): string
    {
        $decoded = base64_decode(strtr($str, '._-', '+/='), true);
        $pairs = str_split($decoded, 2);

        $unscrambled = implode('', array_map(function ($pair) {
            $chars = str_split($pair);
            $randChar = ord($chars[0]);
            $encryptedChar = ord($chars[1]);

            $offset = $encryptedChar - 33;
            $randMod = $randChar % 94;
            $originalOffset = $offset - $randMod;

            if ($originalOffset < 0) {
                $originalOffset += 94;
            }

            return chr($originalOffset + 33);
        }, $pairs));

        return base64_decode($unscrambled, true);
    }
}
