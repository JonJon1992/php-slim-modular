<?php
declare(strict_types=1);

namespace Core\Utils;

class Arr extends \Illuminate\Support\Arr
{
    /**
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    public static function merge(array $arr1, array $arr2): array
    {
        return array_merge($arr1, $arr2);
    }

    /**
     * @param array $arr
     * @param string $key
     * @param mixed $limit
     * @return mixed
     */
    public function min(array $arr, string $key, mixed $limit = null): mixed
    {
        $value = self::get($arr, $key);
        if (is_null($value) || !is_numeric($value) || ($limit && $limit > $value)) return $limit;
        return $value;
    }

    /**
     * @param array $arr
     * @param string $key
     * @param mixed $limit
     * @return mixed
     */
    public function max(array $arr, string $key, mixed $limit = null): mixed
    {
        $value = self::get($arr, $key);
        if (is_null($value) || !is_numeric($value) || ($limit && $limit < $value)) return $limit;
        return $value;
    }

}