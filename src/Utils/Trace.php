<?php

namespace Core\Utils;

use Core\Middleware\TraceMiddleware;
use GuzzleHttp\Client;

class Trace
{
    private static int $sequence = 0;

    private static float $global_started_at;

    private static float $last_logger_at;


    /**
     * @param $global_started_at
     * @return void
     */
    public static function bootstrap($global_started_at): void
    {
        self::$global_started_at = $global_started_at;

        if (empty($_SERVER['HTTP_X_TRACE'])) {
            $_SERVER['HTTP_X_TRACE'] = Str::uuid();
        }
    }


    public static function uuid()
    {
        return $_SERVER['HTTP_X_TRACE'];
    }

    /**
     * @param array $config
     * @return Client
     */
    public static function http(array $config = []): Client
    {
        if (empty($config['headers'])) {
            $config['headers'] = [];
        }
        $config['headers']['x-trace'] = self::uuid();
        return new  Client($config);

    }

    /**
     * @param $value
     * @return string
     */
    private static function format($value): string
    {
        return number_format($value, 10);

    }

    /**
     * @return array
     */
    public static function metadata(): array
    {
        self::$sequence++;

        $logged_at = microtime(true);
        $partial_duration = isset(self::$last_logger_at) ? self::format($logged_at - self::$last_logger_at) : null;
        $middelware_started_at = TraceMiddleware::startedAt();

        $metadata = [
            'uuid' => self::uuid(),
            'sequence' => self::$sequence,
            'logged_at' => $logged_at,
            'partial_duration' => $partial_duration,
            'global' => [
                'started_at' => self::$global_started_at,
                'duration' => self::format($logged_at - self::$global_started_at)
            ]
            , 'middleware' => [
                'started_at' => $middelware_started_at,
                'duration' => $middelware_started_at ? self::format($logged_at - $middelware_started_at) : null
            ]
        ];
        self::$last_logger_at = $logged_at;
        return $metadata;
    }
}