<?php

namespace Core\Utils;

class Time
{
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