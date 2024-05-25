<?php

use Core\Exceptions\ErrorHandler;
use Core\Utils\Env;
use Core\Utils\Str;
use Core\Utils\Trace;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

ob_start(function ($buffer) {
    $error = error_get_last();

    if ($error) {
        $trace = explode("\n", $error['message']);

        $data = [
            'trace-id' => Trace::uuid(),
            'status' => 500,
            'message' => array_shift($trace)
        ];

        $extras = [
            'user-id' => null,
            'status' => $data['status'],
            'method' => filter_input(INPUT_SERVER, 'REQUEST_METHOD'),
            'agent' => filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'),
            'host' => filter_input(INPUT_SERVER, 'HTTP_HOST'),
            'uri' => filter_input(INPUT_SERVER, 'REQUEST_URI'),
            'code' => 0,
            'class' => $error['file'],
            'type' => $error['type'],
            'file' => $error['file'],
            'line' => $error['line'],
            'stack-trace' => $trace,
        ];
        $handler = new StreamHandler(config('log.main.path'), config('app.log_level'));
        $handler->setFormatter(new JsonFormatter());

        $logger = new Logger('Slim');

        $logger->pushProcessor(fn($record) => Trace::processor($record));
        $logger->pushHandler($handler);
        $logger->error($data['message'], $extras);

        if (Env::local()) {
            $data['extras'] = $extras;
        }

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