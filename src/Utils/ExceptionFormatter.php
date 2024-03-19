<?php

namespace Core\Utils;

use Throwable;

class ExceptionFormatter
{
    public static function toArray(Throwable $throwable)
    {
        $debug = [
            'time' => time(),
        ];
        $debug['class'] = get_class($throwable);
        $debug['file'] = $throwable->getFile();
        $debug['line'] = $throwable->getLine();
        $debug['trace'] = explode("\n", $throwable->getTraceAsString());

        return $debug;
    }
}
