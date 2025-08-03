<?php

namespace Core\Utils;

use Core\Utils\Str;

class Trace
{
    public static function processor($record): array
    {
        $extras = $record['context'];

        $extras['user-id'] = auth()->id();

        $data = [
            'timestamp' => self::timestamp(),
            'severity' => $record['level_name'],

            'env' => self::env(),
            'appname' => self::appname(),
            'message' => $record['message'],

            'extras' => $extras,

            'trace-id' => self::uuid(),
        ];

        $data['level'] = $record['level'];

        return $data;
    }

    /**
     * @return void
     */
    public static function bootstrap(): void
    {
        if (empty($_SERVER['HTTP_TRACE_ID'])) {
            $_SERVER['HTTP_TRACE_ID'] = empty($_SERVER['HTTP_X_GRACE']) ? Str::uuid() : $_SERVER['HTTP_X_GRACE'];
        }
    }

    /**
     * @return string
     */
    public static function uuid(): string
    {
        return $_SERVER['HTTP_TRACE_ID'];
    }

    /**
     * @return string
     */
    private static function env(): string
    {
        return Env::prod() ? 'PRD' : (Env::stg() ? 'STG' : 'DEV');
    }

    /**
     * @return string
     */
    private static function appname(): string
    {
        return env('APP_NAME', 'APP_NAME') . '/' . (app()->module() ?? 'main');
    }

    /**
     * @return string
     */
    private static function timestamp(): string
    {
        return (new \DateTime())->format('Y-m-d\TH:i:s.uP');
    }

    /**
     * @param array $config
     * @return \GuzzleHttp\Client
     */
    public static function http(array $config = []): \GuzzleHttp\Client
    {
        if (empty($config['headers'])) {
            $config['headers'] = [];
        }

        $config['headers']['trace-id'] = self::uuid();

        return new \GuzzleHttp\Client($config);
    }
}