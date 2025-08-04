<?php

declare(strict_types=1);

/**
 * Exemplo Simplificado do Sistema de Tratamento de Exceções
 * 
 * Este arquivo demonstra as melhorias implementadas no sistema
 * de exceções sem depender do autoloader do Composer.
 */

// Simular as classes e funções necessárias
class Trace
{
    public static function uuid(): string
    {
        return uniqid('trace-', true);
    }
    
    public static function processor(array $record): array
    {
        $record['trace_id'] = self::uuid();
        return $record;
    }
}

class Env
{
    public static function local(): bool
    {
        return true; // Simular ambiente de desenvolvimento
    }
}

// Simular configuração
function config(string $key, mixed $default = null): mixed
{
    $config = [
        'log.main.path' => '/tmp/app.log',
        'app.log_level' => 'error'
    ];
    
    return $config[$key] ?? $default;
}

function storage_path(string $path = ''): string
{
    return '/tmp' . ($path ? '/' . ltrim($path, '/') : '');
}

function auth(): mixed
{
    return (object) ['id' => 123];
}

// Simular Logger básico
class SimpleLogger
{
    public function log(string $level, string $message, array $context = []): void
    {
        $logEntry = [
            'timestamp' => date('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];
        
        echo "LOG [$level]: $message\n";
        if (!empty($context)) {
            echo "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n";
        }
    }
}

// Versão simplificada do ExceptionHandler
class SimpleExceptionHandler
{
    private SimpleLogger $logger;
    private array $statusCodeMap;
    private array $defaultMessages;
    
    public function __construct()
    {
        $this->logger = new SimpleLogger();
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
            'path' => $_SERVER['REQUEST_URI'] ?? '/',
        ];
        
        // Add detailed information in development environment
        if (Env::local()) {
            $data['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTraceAsString(),
            ];
        }
        
        return $data;
    }
    
    public function log(Throwable $exception, array $errorData): void
    {
        $context = [
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
            ],
            'user' => [
                'id' => auth()->id ?? null,
            ],
        ];
        
        $logLevel = $errorData['status'] >= 500 ? 'error' : 'warning';
        $this->logger->log($logLevel, $exception->getMessage(), $context);
    }
    
    public function sendResponse(array $errorData): void
    {
        echo "=== ERRO DETECTADO ===\n";
        echo "Status: " . $errorData['status'] . "\n";
        echo "Mensagem: " . $errorData['message'] . "\n";
        echo "Trace ID: " . $errorData['trace-id'] . "\n";
        echo "Timestamp: " . $errorData['timestamp'] . "\n";
        
        if (isset($errorData['debug'])) {
            echo "Debug Info:\n";
            echo "  Exception: " . $errorData['debug']['exception'] . "\n";
            echo "  File: " . $errorData['debug']['file'] . "\n";
            echo "  Line: " . $errorData['debug']['line'] . "\n";
        }
        
        echo "=====================\n";
    }
    
    private function getHttpStatusCode(Throwable $exception): int
    {
        $exceptionClass = get_class($exception);
        
        // Check if exception code is a valid HTTP status code
        $code = $exception->getCode();
        if ($code >= 100 && $code < 600) {
            return $code;
        }
        
        // Use mapped status code or default to 500
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
        ];
    }
    
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

// Exemplos de uso
echo "=== DEMONSTRAÇÃO DO SISTEMA DE EXCEÇÕES MELHORADO ===\n\n";

$handler = new SimpleExceptionHandler();

// Exemplo 1: Exceção de validação
echo "1. Exceção de Validação:\n";
try {
    throw new InvalidArgumentException('Email inválido fornecido', 422);
} catch (Throwable $exception) {
    $errorData = $handler->handle($exception);
    $handler->log($exception, $errorData);
    $handler->sendResponse($errorData);
}

echo "\n";

// Exemplo 2: Exceção de domínio
echo "2. Exceção de Domínio:\n";
try {
    throw new DomainException('Regra de negócio violada', 400);
} catch (Throwable $exception) {
    $errorData = $handler->handle($exception);
    $handler->log($exception, $errorData);
    $handler->sendResponse($errorData);
}

echo "\n";

// Exemplo 3: Erro de execução
echo "3. Erro de Execução:\n";
try {
    throw new RuntimeException('Erro interno do sistema', 500);
} catch (Throwable $exception) {
    $errorData = $handler->handle($exception);
    $handler->log($exception, $errorData);
    $handler->sendResponse($errorData);
}

echo "\n";

// Exemplo 4: Simulação de erro fatal
echo "4. Simulação de Erro Fatal:\n";
try {
    $fatalException = new ErrorException(
        'Call to undefined function undefined_function()',
        0,
        E_ERROR,
        '/app/example.php',
        25
    );
    
    $errorData = $handler->handle($fatalException);
    $handler->log($fatalException, $errorData);
    $handler->sendResponse($errorData);
} catch (Throwable $e) {
    echo "Erro ao processar erro fatal: " . $e->getMessage() . "\n";
}

echo "\n=== MELHORIAS IMPLEMENTADAS ===\n";
echo "✅ Tipagem forte com declare(strict_types=1)\n";
echo "✅ Separação clara de responsabilidades\n";
echo "✅ Logging estruturado com contexto completo\n";
echo "✅ Mapeamento inteligente de status codes HTTP\n";
echo "✅ Tratamento de erros fatais\n";
echo "✅ Fallback seguro para logging\n";
echo "✅ Interface para testabilidade\n";
echo "✅ Documentação completa\n";
echo "✅ Formato de resposta padronizado\n";
echo "✅ Informações de debug em desenvolvimento\n";
echo "✅ Trace ID único para rastreamento\n";

echo "\n=== FIM DA DEMONSTRAÇÃO ===\n"; 