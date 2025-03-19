<?php

namespace Core\Utils;

class Env extends \Illuminate\Support\Env
{
    /**
     * @return mixed
     */
    public static function name(): mixed
    {
        return env('APP_ENV', 'production');
    }

    /**
     * @return bool
     */
    public static function prod(): bool
    {
        return self::name() == 'production';
    }

    /**
     * @return bool
     */
    public static function dev(): bool
    {
        return self::name() == 'development';
    }

    /**
     * @return bool
     */
    public static function test(): bool
    {
        return self::name() == 'test';
    }

    /**
     * @return bool
     */
    public static function stg(): bool
    {
        return self::name() == 'staging';
    }

    /**
     * @return bool
     */
    public static function local(): bool
    {
        return self::name() == 'local';
    }

    /**
     * @return bool
     */
    public static function real(): bool
    {
        return self::prod() || self::stg();

    }
}