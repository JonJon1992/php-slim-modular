<?php

declare(strict_types=1);

namespace Core\Contracts\Exception;

use Throwable;

interface IExceptionHandler
{
    /**
     * Handle an exception and return formatted response data
     * 
     * @param Throwable $exception The exception to handle
     * @return array Formatted error response data
     */
    public function handle(Throwable $exception): array;

    /**
     * Log exception with context
     * 
     * @param Throwable $exception The exception to log
     * @param array $errorData The error response data
     */
    public function log(Throwable $exception, array $errorData): void;

    /**
     * Send error response to client
     * 
     * @param array $errorData The error response data to send
     */
    public function sendResponse(array $errorData): void;
}
