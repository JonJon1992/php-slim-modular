<?php

declare(strict_types=1);

namespace Core\Exceptions;

use Core\Contracts\Exception\IExceptionHandler;
use Core\Utils\Env;
use Core\Utils\Trace;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Throwable;

/**
 * Error Handler for Slim Framework
 * 
 * Extends Slim's ErrorHandler to provide consistent error handling
 * with the application's exception handling system.
 */
class ErrorHandler extends SlimErrorHandler
{
    private IExceptionHandler $exceptionHandler;

    public function __construct($callableResolver, $responseFactory, IExceptionHandler $exceptionHandler = null)
    {
        parent::__construct($callableResolver, $responseFactory);
        $this->exceptionHandler = $exceptionHandler ?? new ExceptionHandler();
    }

    /**
     * Handle the error and return a response
     */
    public function respond(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();

        // Handle OPTIONS requests for CORS
        if ($this->request->getMethod() === 'OPTIONS' && !$this->exception instanceof HttpNotFoundException) {
            return $response->withStatus(200);
        }

        // Use the exception handler to process the error
        $errorData = $this->exceptionHandler->handle($this->exception ?? new \RuntimeException('Unknown error'));
        
        // Add Slim-specific information
        $errorData = $this->addSlimSpecificData($errorData);
        
        // Log the error
        if ($this->exception) {
            $this->exceptionHandler->log($this->exception, $errorData);
        }

        // Add debug information in development
        if (Env::local()) {
            $errorData['debug'] = $this->getDebugInfo($errorData);
        }

        // Write response
        $response->getBody()->write(json_encode($errorData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', '0')
            ->withStatus($errorData['status']);
    }

    /**
     * Add Slim-specific data to the error response
     */
    private function addSlimSpecificData(array $errorData): array
    {
        // Add request information
        $errorData['request'] = [
            'method' => $this->request->getMethod(),
            'uri' => (string) $this->request->getUri(),
            'user_agent' => $this->request->getHeaderLine('User-Agent'),
            'host' => $this->request->getHeaderLine('Host'),
            'ip' => $this->getClientIp(),
        ];

        // Add user information if available
        if (function_exists('auth') && auth()) {
            $errorData['user'] = [
                'id' => auth()->id() ?? null,
            ];
        }

        // Add specific information for HTTP method not allowed
        if ($this->exception instanceof HttpMethodNotAllowedException) {
            $errorData['allowed_methods'] = $this->exception->getAllowedMethods();
        }

        return $errorData;
    }

    /**
     * Get debug information for development environment
     */
    private function getDebugInfo(array $errorData): array
    {
        $debug = [];

        if ($this->exception) {
            $debug['exception'] = [
                'class' => get_class($this->exception),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
                'code' => $this->exception->getCode(),
                'trace' => $this->exception->getTraceAsString(),
            ];

            if ($this->exception->getPrevious()) {
                $debug['exception']['previous'] = [
                    'class' => get_class($this->exception->getPrevious()),
                    'message' => $this->exception->getPrevious()->getMessage(),
                ];
            }
        }

        // Add request details
        $debug['request'] = [
            'method' => $this->request->getMethod(),
            'uri' => (string) $this->request->getUri(),
            'headers' => $this->request->getHeaders(),
            'query_params' => $this->request->getQueryParams(),
        ];

        // Add response details
        $debug['response'] = [
            'status_code' => $errorData['status'] ?? 500,
            'headers' => [],
        ];

        return $debug;
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $serverParams = $this->request->getServerParams();
        
        // Check for forwarded IP headers
        $forwardedHeaders = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
        ];

        foreach ($forwardedHeaders as $header) {
            if (isset($serverParams[$header])) {
                $ips = explode(',', $serverParams[$header]);
                return trim($ips[0]);
            }
        }

        return $serverParams['REMOTE_ADDR'] ?? 'unknown';
    }
}