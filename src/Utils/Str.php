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

}