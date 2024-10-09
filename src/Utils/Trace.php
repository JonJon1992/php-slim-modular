<?php

namespace Core\Utils;

use DateTime;
use GuzzleHttp\Client;

class Trace
{
    /**
     * @return void
     */
    public static function bootstrap(): void
    {
        if (empty($_SERVER['HTTP_TRACE_ID'])) {
            $_SERVER['HTTP_TRACE_ID'] = empty($_SERVER['HTTP_X_TRACE']) ? Str::uuid() : $_SERVER['HTTP_X_TRACE'];
        }
    }

    /**
     * @return mixed
     */
    public static function uuid(): mixed
    {
        return $_SERVER['HTTP_TRACE_ID'];
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
        $config['headers']['trace-id'] = self::uuid();
        return new  Client($config);

    }


    /**
     * @param $record
     * @return array
     */
    public static function processor($record): array
    {
        $extras = $record['context'];

        $extras['user-id'] = auth()->id();

        $data = [
            'timestamp' => self::timestamp(),
            'severity' => $record['level_name'],
            'env' => self::env(),
            'appname' => self::appname(),
            'extras' => $extras,
            'trace-id' => self::uuid()
        ];

        $data['level'] = $record['level'];
        return $data;
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
        return (new DateTime())->format('Y-m-d\TH:i:s.uP');
    }
}