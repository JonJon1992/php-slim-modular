<?php

namespace Core\Utils;

class Arr extends \Illuminate\Support\Arr
{
    /**
     * @param $arr1
     * @param $arr2
     * @return array
     */
    public static function merge($arr1, $arr2)
    {
        return array_merge($arr1, $arr2);
    }

}