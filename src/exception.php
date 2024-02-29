<?php

use Core\Utils\Str;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

ob_start(function ($buffer) {
    $error = error_get_last();

    if ($error) {
        $trace = explode("\n", $error['message']);

        $data = [
            'code' => 500,
            'message' => array_shift($trace),
            'uuid' => Str::uuid(),
            'debug' => [
                'type' => $error['type'],
                'file' => $error['file'],
                'line' => $error['line'],
                'trace' => $trace,
                'request' => ErrorHandler::metadados()
            ]
        ];

        $handler = new StreamHandler(config('log.main.path'), config('app.log_level'));
        $handler->setFormatter(new JsonFormatter());

        $logger = new Logger('Slim Modular');

        $logger->pushProcessor(function ($record) {
            $record['extra']['trace'] = Trace::metadados();
            return $record;
        });
        $logger->pushHandler($handler);
        $logger->error('ErrorHandler', $data);

        header('Content-type: application/json');
        http_response_code(500);
        return json_encode($data, JSON_PRETTY_PRINT);
    }
    return $buffer;
});

set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw  new ErrorException($message, 0, $severity, $file, $line);
    }
});

if (env('APP_ENV', 'production') == 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}