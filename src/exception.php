<?php

declare(strict_types=1);

use Core\Exceptions\ExceptionHandler;



$exceptionHandler = new ExceptionHandler();

set_exception_handler(function (Throwable $exception) use ($exceptionHandler): void {
    $errorData = $exceptionHandler->handle($exception);
    $exceptionHandler->log($exception, $errorData);
    $exceptionHandler->sendResponse($errorData);
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(function () use ($exceptionHandler): void {
    $error = error_get_last();

    if ($error !== null && isFatalError($error['type'])) {
        $exception = new ErrorException(
            $error['message'],
            0,
            $error['type'],
            $error['file'],
            $error['line']
        );

        $errorData = $exceptionHandler->handle($exception);
        $exceptionHandler->log($exception, $errorData);
        $exceptionHandler->sendResponse($errorData);
    }
});

if (isDevelopmentEnvironment()) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}
function isFatalError(int $type): bool
{
    return in_array($type, [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_CORE_WARNING,
        E_COMPILE_ERROR,
        E_COMPILE_WARNING,
        E_USER_ERROR,
    ]);
}

function isDevelopmentEnvironment(): bool
{
    return getEnvVar('APP_ENV', 'production') === 'development' ||
        getEnvVar('APP_DEBUG', false) === true;
}

function getEnvVar(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}
