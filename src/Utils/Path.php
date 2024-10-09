<?php

namespace Core\Utils;

use Composer\Autoload\ClassLoader;
use ReflectionClass;

class Path
{
    /**
     * @param $base
     * @param $path
     * @return string
     */
    protected static function resolve($base, $path = null): string
    {
        return $base . $path ?? '';
    }

    /**
     * @param $path
     * @return string
     */
    public static function root($path = null): string
    {
        $reflection = new ReflectionClass(ClassLoader::class);
        return self::resolve(dirname($reflection->getFileName()) . '/../..', $path);
    }

    /**
     * @param $path
     * @return string
     */
    public static function cache($path = null): string
    {
        return self::resolve(self::root('/var/cache'), $path);
    }

    /**
     * @param $path
     * @return string
     */
    public static function configs($path = null): string
    {
        return self::resolve(self::root('/configs'), $path);
    }

    /**
     * @param $path
     * @return string
     */
    public static function logs($path = null): string
    {
        return self::resolve(self::root('/logs'), $path);
    }

    /**
     * @param $path
     * @return string
     */
    public static function modules($path = null): string
    {
        return self::resolve(self::root('/modules'), $path);
    }

    /**
     * @param $path
     * @return string
     */
    public static function public($path = null): string
    {
        return self::resolve(self::root('/public'), $path);
    }

    /**
     * @param $path
     * @return string
     */
    public static function temp($path = null): string
    {
        return self::resolve(self::root('/var/temp'), $path);
    }

    /**
     * @param $path
     * @return string
     */
    public static function var($path = null): string
    {
        return self::resolve(self::root('/var'), $path);
    }
}