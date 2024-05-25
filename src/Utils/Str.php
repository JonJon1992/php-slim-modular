<?php

namespace Core\Utils;

use Ramsey\Uuid\UuidInterface;

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
}