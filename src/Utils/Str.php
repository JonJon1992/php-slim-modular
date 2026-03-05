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
     * Gera ID sequencial baseado em microtime (ordenável por tempo de criação).
     *
     * @return string
     */
    public static function sequentialId(): string
    {
        return sprintf('%d%04d', (int) (microtime(true) * 10000), random_int(0, 9999));
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