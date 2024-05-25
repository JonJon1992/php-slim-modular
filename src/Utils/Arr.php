<?php

namespace Core\Utils;

class Arr extends \Illuminate\Support\Arr
{
    /**
     * @param $arr1
     * @param $arr2
     * @return array
     */
    public static function merge($arr1, $arr2): array
    {
        return array_merge($arr1, $arr2);
    }

    /**
     * @param $arr
     * @param $key
     * @param $limit
     */
    public function min($arr, $key, $limit = null)
    {
        $value = self::get($arr, $key);
        if (is_null($value) || !is_numeric($value) || ($limit && $limit > $value)) return $limit;
        return $value;
    }

    /**
     * @param $arr
     * @param $key
     * @param $limit
     */
    public function max($arr, $key, $limit = null)
    {
        $value = self::get($arr, $key);
        if (is_null($value) || !is_numeric($value) || ($limit && $limit < $value)) return $limit;
        return $value;
    }

}