<?php

declare(strict_types=1);

namespace Core\Utils;
use ArrayAccess;
use DateTimeInterface;
use GuzzleHttp\Client;

class Trace
{
    private const TIMESTAMP_FORMAT = 'Y-m-d\TH:i:s.uP';

    /**
     * Campos de uma linha de log do framework. Aceita o registro do Monolog 2 (array) e o do
     * Monolog 3 (LogRecord, que também pode ser lido como array).
     *
     * @param array|ArrayAccess $record
     * @return array
     */
    public static function logData(array|ArrayAccess $record): array
    {
        $extras = $record['context'];
        $extras['user-id'] = auth()->id();

        $datetime = $record['datetime'] ?? null;

        return [
            'timestamp' => $datetime instanceof DateTimeInterface
                ? $datetime->format(self::TIMESTAMP_FORMAT)
                : self::timestamp(),
            'severity' => $record['level_name'],
            'env' => self::env(),
            'appname' => self::appname(),
            'message' => $record['message'],
            'extras' => $extras,
            'trace-id' => self::uuid(),
            'level' => $record['level'],
        ];
    }

    /**
     * @deprecated desde 3.3.0, será removido na 4.0. Substitui o registro inteiro, o que o
     *             Monolog 3 não permite; use o TraceJsonFormatter no handler.
     */
    public static function processor($record): array
    {
        return self::logData($record);
    }

    /**
     * @return void
     */
    public static function bootstrap(): void
    {
        if (empty($_SERVER['HTTP_TRACE_ID'])) {
            $_SERVER['HTTP_TRACE_ID'] = empty($_SERVER['HTTP_X_GRACE']) ? Str::sequentialId() : $_SERVER['HTTP_X_GRACE'];
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
        return (new \DateTime())->format(self::TIMESTAMP_FORMAT);
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

        return new Client($config);
    }
}
