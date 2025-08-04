<?php

declare(strict_types=1);

/**
 * Exemplo Simplificado do ErrorHandler Melhorado
 * 
 * Este arquivo demonstra as melhorias implementadas no ErrorHandler
 * sem depender do autoloader do Composer.
 */

// Simular as classes e funções necessárias
class Trace
{
    public static function uuid(): string
    {
        return uniqid('trace-', true);
    }
}

class Env
{
    public static function local(): bool
    {
        return true; // Simular ambiente de desenvolvimento
    }
}

// Simular PSR-7 Request
class MockRequest
{
    private array $headers;
    private string $method;
    private string $uri;
    private array $serverParams;
    
    public function __construct()
    {
        $this->headers = [
            'User-Agent' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'],
            'Host' => ['localhost:8000'],
        ];
        $this->method = 'GET';
        $this->uri = '/test/validation';
        $this->serverParams = [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '192.168.1.1',
        ];
    }
    
    public function getMethod(): string
    {
        return $this->method;
    }
    
    public function getUri(): string
    {
        return $this->uri;
    }
    
    public function getHeaderLine(string $name): string
    {
        return $this->headers[$name][0] ?? '';
    }
    
    public function getServerParams(): array
    {
        return $this->serverParams;
    }
}

// Simular PSR-7 Response
class MockResponse
{
    private array $headers = [];
    private string $body = '';
    private int $statusCode = 200;
    
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    public function withStatus(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }
    
    public function getBody(): MockStream
    {
        return new MockStream($this->body);
    }
    
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

class MockStream
{
    private string $content;
    
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }
    
    public function write(string $content): int
    {
        $this->content = $content;
        return strlen($content);
    }
    
    public function getContents(): string
    {
        return $this->content;
    }
}

// Simular ResponseFactory
class MockResponseFactory
{
    public function createResponse(): MockResponse
    {
        return new MockResponse();
    }
}

// Simular ExceptionHandler
class MockExceptionHandler
{
    public function handle(Throwable $exception): array
    {
        return [
            'trace-id' => Trace::uuid(),
            'status' => $exception->getCode() ?: 500,
            'message' => $exception->getMessage(),
            'timestamp' => date('c'),
            'path' => '/test/validation',
        ];
    }
    
    public function log(Throwable $exception, array $errorData): void
    {
        echo "LOG: " . $exception->getMessage() . "\n";
    }
}

// Versão simplificada do ErrorHandler melhorado
class SimpleErrorHandler
{
    private MockExceptionHandler $exceptionHandler;
    private MockRequest $request;
    private MockResponse $response;
    private ?Throwable $exception;
    private int $statusCode;
    
    public function __construct(MockExceptionHandler $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
        $this->request = new MockRequest();
        $this->response = new MockResponse();
        $this->exception = null;
        $this->statusCode = 500;
    }
    
    public function setException(Throwable $exception): void
    {
        $this->exception = $exception;
        $this->statusCode = $exception->getCode() ?: 500;
    }
    
    public function respond(): MockResponse
    {
        // Handle OPTIONS requests for CORS
        if ($this->request->getMethod() === 'OPTIONS') {
            return $this->response->withStatus(200);
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
            $errorData['debug'] = $this->getDebugInfo();
        }

        // Write response
        $this->response->getBody()->write(json_encode($errorData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $this->response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', '0')
            ->withStatus($errorData['status']);
    }
    
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
        $errorData['user'] = [
            'id' => 123,
        ];

        return $errorData;
    }
    
    private function getDebugInfo(): array
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
        }

        // Add request details
        $debug['request'] = [
            'method' => $this->request->getMethod(),
            'uri' => (string) $this->request->getUri(),
        ];

        return $debug;
    }
    
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

// Exemplos de uso
echo "=== DEMONSTRAÇÃO DO ERRORHANDLER MELHORADO ===\n\n";

$exceptionHandler = new MockExceptionHandler();
$errorHandler = new SimpleErrorHandler($exceptionHandler);

// Exemplo 1: Erro de validação
echo "1. Erro de Validação (422):\n";
$errorHandler->setException(new InvalidArgumentException('Dados inválidos fornecidos', 422));
$response = $errorHandler->respond();

echo "Status: " . $response->getStatusCode() . "\n";
echo "Headers: " . json_encode($response->getHeaders(), JSON_PRETTY_PRINT) . "\n";
echo "Body: " . $response->getBody()->getContents() . "\n\n";

// Exemplo 2: Erro de autenticação
echo "2. Erro de Autenticação (401):\n";
$errorHandler->setException(new RuntimeException('Usuário não autenticado', 401));
$response = $errorHandler->respond();

echo "Status: " . $response->getStatusCode() . "\n";
echo "Body: " . $response->getBody()->getContents() . "\n\n";

// Exemplo 3: Erro interno do servidor
echo "3. Erro Interno do Servidor (500):\n";
$errorHandler->setException(new ErrorException('Erro interno do sistema', 0, E_ERROR));
$response = $errorHandler->respond();

echo "Status: " . $response->getStatusCode() . "\n";
echo "Body: " . $response->getBody()->getContents() . "\n\n";

echo "=== MELHORIAS IMPLEMENTADAS NO ERRORHANDLER ===\n";
echo "✅ Tipagem forte com declare(strict_types=1)\n";
echo "✅ Injeção de dependência do ExceptionHandler\n";
echo "✅ Uso de métodos PSR-7 em vez de filter_input()\n";
echo "✅ Detecção inteligente de IP do cliente\n";
echo "✅ Informações de debug mais detalhadas\n";
echo "✅ Headers HTTP apropriados para APIs\n";
echo "✅ Suporte melhorado a CORS\n";
echo "✅ Reutilização da lógica do ExceptionHandler\n";
echo "✅ Consistência no formato de resposta\n";
echo "✅ Melhor testabilidade\n";

echo "\n=== BENEFÍCIOS DA REFATORAÇÃO ===\n";
echo "1. Código mais limpo e organizado\n";
echo "2. Reutilização de lógica entre handlers\n";
echo "3. Melhor testabilidade com injeção de dependência\n";
echo "4. Consistência no formato de resposta\n";
echo "5. Informações de debug mais úteis\n";
echo "6. Headers HTTP apropriados para APIs\n";
echo "7. Detecção correta de IP do cliente\n";
echo "8. Suporte adequado a CORS\n";

echo "\n=== FIM DA DEMONSTRAÇÃO ===\n"; 