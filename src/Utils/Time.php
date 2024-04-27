<?php

namespace Core\Utils;

class Time
{
    const ACCESS_LIFETIME = 24 * 60 * 60;
    const REFRESH_LIFETIME = 24 * 60 * 60 * 7;
    const REMINDER_LIFETIME = 24 * 60 * 60 * 30;
    const _24H = 86400;

    /**
     * @param float $start
     * @param int $precision
     * @return float
     */
    public static function latency(float $start, int $precision = 6): float
    {
        return round(microtime(true) - $start, $precision);
    }

    /**
     * @param $time
     * @param int $min
     * @param int $max
     * @param int $delay
     * @return bool
     */
    public static function validate($time, int $min = 5, int $max = 5, int $delay = 5): bool
    {
        if (!is_numeric($time) || !is_int($time + 0)) {
            return false;
        }
        $min = time() - $min - $delay;
        $max = time() + $max + $delay;

        return $time <= $max && $time >= $min;
    }

}