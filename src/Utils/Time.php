<?php

namespace Core\Utils;

class Time
{
    const ACCESS_LIFETIME = 24 * 60 * 60;
    const REFRESH_LIFETIME = 24 * 60 * 60 * 7;
    const REMINDER_LIFETIME = 24 * 60 * 60 * 30;
    const _10S = 10;
    const _20S = 20;
    const _30S = 30;
    const _40S = 50;
    const _50S = 50;
    const _60S = 60;

    const _1M = 60;
    const _5M = 300;
    const _10M = 600;
    const _20M = 1200;
    const _30M = 1800;
    const _40M = 2400;
    const _50M = 3000;
    const _60M = 3600;

    const _1H = 3600;
    const _2H = 7200;
    const _3H = 10800;
    const _4H = 14400;
    const _5H = 18000;
    const _6H = 21600;
    const _12H = 43200;
    const _18H = 64800;
    const _24H = 86400;

    const _1D = 86400;
    const _2D = 172800;
    const _3D = 259200;
    const _4D = 345600;
    const _5D = 432000;
    const _6D = 518400;
    const _7D = 604800;

    const _1W = 604800;
    const _2W = 1209600;
    const _3W = 1814400;
    const _4W = 2419200;

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
     * @param int|float|string $time
     * @param int $min
     * @param int $max
     * @param int $delay
     * @return bool
     */
    public static function validate($time, int $min = 5, int $max = 5, int $delay = 5): bool
    {
        if (!is_numeric($time)) {
            return false;
        }

        $timeValue = (int)$time;
        $currentTime = time();
        $minTime = $currentTime - $min - $delay;
        $maxTime = $currentTime + $max + $delay;

        return $timeValue >= $minTime && $timeValue <= $maxTime;
    }

}