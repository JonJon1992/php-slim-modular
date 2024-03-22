<?php

namespace Core\Utils;

class Time
{
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

}