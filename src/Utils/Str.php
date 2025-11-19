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
        $limit = (10 ** $size) - 1;
        $random = random_int(0, $limit);
        return str_pad((string)$random, $size, '0', STR_PAD_LEFT);
    }

    /**
     * @param int $length
     * @return string
     */
    public static function uuid(int $length = 16): string
    {
        $data = random_bytes($length);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * @param $value
     * @return array|string|null
     */
    public static function numbers($value): array|string|null
    {
        return preg_replace('/[^0-9]/', '', (string)$value);
    }

    /**
     * @param string $value
     * @param string $cap
     * @return bool
     */
    public static function endsWith($value, $cap): bool
    {
        return is_string($value) && is_string($cap) && str_ends_with($value, $cap);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function camel($value): string
    {
        return lcfirst(static::studly($value));
    }

    /**
     * @param string $value
     * @return string
     */
    public static function ucfirst($value): string
    {
        return ucfirst($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function studly($value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return str_replace(' ', '', $value);
    }
}