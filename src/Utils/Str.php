<?php

namespace Core\Utils;

class Str extends \Illuminate\Support\Str
{
    /**
     * @param $value
     * @param $needle
     * @return bool
     */
    public static function has($value, $needle): bool
    {
        return str_contains($value, $needle);
    }

    /**
     * @param int $size
     * @return string
     */
    public static function code(int $size = 6): string
    {
        $limit = str_pad('', $size, STR_PAD_LEFT);
        return str_pad(rand(0, $limit), $size, '0', STR_PAD_LEFT);

    }
}