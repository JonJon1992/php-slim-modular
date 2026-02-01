<?php

declare(strict_types=1);

namespace Core\Exceptions;

use Core\Contracts\Exception\IExceptionHandler;
use Core\Utils\Env;
use Core\Utils\Path;
use Core\Utils\Trace;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Throwable;

class ExceptionHandler implements IExceptionHandler
{
    private LoggerInterface $logger;
    private array $statusCodeMap;
    private array $defaultMessages;

    public function __construct()
    {
        $this->logger = $this->createLogger();
        $this->statusCodeMap = $this->getStatusCodeMap();
        $this->defaultMessages = $this->getDefaultMessages();
    }

    public function handle(Throwable $exception): array
    {
        $statusCode = $this->getHttpStatusCode($exception);

        $data = [
            'trace-id' => Trace::uuid(),
            'status' => $statusCode,
            'message' => $this->getErrorMessage($exception, $statusCode),
            'timestamp' => date('c'),
            'path' => $this->getRequestPath(),
        ];

        // Add detailed information in development environment
        if (Env::local()) {
            $data['debug'] = $this->getDebugInfo($exception);
        }

        return $data;
    }

    public function log(Throwable $exception, array $errorData): void
    {
        try {
            $context = $this->createLogContext($exception, $errorData);
            $logLevel = $errorData['status'] >= 500 ? 'error' : 'warning';

            $this->logger->log($logLevel, $exception->getMessage(), $context);
        } catch (Throwable $logException) {
            // Fallback logging to prevent infinite loops
            error_log(sprintf(
                'Error logging failed: %s. Original error: %s',
                $logException->getMessage(),
                $exception->getMessage()
            ));
        }
    }

    public function sendResponse(array $errorData): void
    {
        if (headers_sent()) {
            return; // Headers already sent, can't modify response
        }

        // Clear any existing output
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set response headers
        http_response_code($errorData['status']);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Send JSON response
        echo json_encode($errorData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit(1);
    }

    /**
     * Create logger instance with proper configuration
     */
    private function createLogger(): LoggerInterface
    {
        $logger = new Logger('Application');

        try {
            $logPath = config('log.main.path', Path::logs('/app.log'));
            $logLevel = config('app.log_level', 'error');

            $handler = new StreamHandler($logPath, $logLevel);
            $handler->setFormatter(new JsonFormatter());

            $logger->pushHandler($handler);
            $logger->pushProcessor(fn(array $record) => Trace::processor($record));
        } catch (Throwable $e) {
            // Fallback to basic error logging
            error_log('Failed to configure logger: ' . $e->getMessage());
        }

        return $logger;
    }

    /**
     * Create context data for logging
     */
    private function createLogContext(Throwable $exception, array $errorData): array
    {
        return [
            'trace_id' => $errorData['trace-id'],
            'status_code' => $errorData['status'],
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'request' => [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'host' => $_SERVER['HTTP_HOST'] ?? '',
            ],
            'user' => [
                'id' => auth()->id() ?? null,
            ],
            'stack_trace' => $exception->getTraceAsString(),
        ];
    }

    /**
     * Get appropriate HTTP status code for exception
     */
    private function getHttpStatusCode(Throwable $exception): int
    {
        $exceptionClass = get_class($exception);

        $code = (int) $exception->getCode();
        if ($code >= 100 && $code < 600) {
            return $code;
        }

        return $this->statusCodeMap[$exceptionClass] ?? 500;
    }


    private function getErrorMessage(Throwable $exception, int $statusCode): string
    {
        // Use exception message if it's not empty
        if (!empty($exception->getMessage())) {
            return $exception->getMessage();
        }

        return $this->defaultMessages[$statusCode] ?? 'Erro inesperado.';
    }

    /**
     * Get debug information for development environment
     */
    private function getDebugInfo(Throwable $exception): array
    {
        return [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString(),
            'previous' => $exception->getPrevious() ? [
                'class' => get_class($exception->getPrevious()),
                'message' => $exception->getPrevious()->getMessage(),
            ] : null,
        ];
    }

    /**
     * Get current request path
     */
    private function getRequestPath(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    /**
     * Get status code mapping for exception types
     */
    private function getStatusCodeMap(): array
    {
        return [
            'InvalidArgumentException' => 400,
            'DomainException' => 400,
            'LogicException' => 400,
            'RuntimeException' => 500,
            'ErrorException' => 500,
            'ParseError' => 500,
            'TypeError' => 500,
            'ArgumentCountError' => 500,
            'DivisionByZeroError' => 500,
            'ArithmeticError' => 500,
            'AssertionError' => 500,
            'Core\Exceptions\AppException' => 400,
            'Core\Exceptions\Exception\DomainException' => 400,
            'Core\Exceptions\Exception\InfraException' => 500,
            'Core\Exceptions\Exception\ValidatorException' => 422,
            'Core\Exceptions\Exception\AuthenticationException' => 401,
            'Core\Exceptions\Exception\InvalidRequestException' => 400,
        ];
    }

    /**
     * Get default error messages by status code
     */
    private function getDefaultMessages(): array
    {
        return [
            400 => 'Requisição inválida.',
            401 => 'Não autorizado.',
            403 => 'Acesso negado.',
            404 => 'Recurso não encontrado.',
            405 => 'Método não permitido.',
            422 => 'Dados inválidos.',
            429 => 'Muitas requisições.',
            500 => 'Erro interno do servidor.',
            502 => 'Erro de gateway.',
            503 => 'Serviço indisponível.',
            504 => 'Timeout do gateway.',
        ];
    }
}
