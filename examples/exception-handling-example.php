<?php

declare(strict_types=1);

/**
 * Exemplo de Uso do Sistema de Tratamento de Exceções
 * 
 * Este arquivo demonstra como o novo sistema de exceções funciona
 * e como pode ser usado em diferentes cenários.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Exceptions\ExceptionHandler;
use Core\Exceptions\ValidatorException;
use Core\Exceptions\AuthenticationException;
use Core\Exceptions\DomainException;

// Simular configuração básica
if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed {
        return $default;
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string {
        return __DIR__ . '/../storage' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('auth')) {
    function auth(): mixed {
        return (object) ['id' => 123];
    }
}

// Exemplo 1: Tratamento automático de exceções
echo "=== Exemplo 1: Tratamento Automático ===\n";

try {
    // Simular uma exceção de validação
    throw new ValidatorException('Email inválido fornecido');
} catch (ValidatorException $e) {
    echo "Exceção capturada: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
}

// Exemplo 2: Uso manual do ExceptionHandler
echo "\n=== Exemplo 2: Uso Manual do ExceptionHandler ===\n";

$handler = new ExceptionHandler();

try {
    // Simular uma operação que pode falhar
    $result = riskyOperation();
    echo "Operação bem-sucedida: " . $result . "\n";
} catch (Throwable $exception) {
    echo "Erro capturado: " . $exception->getMessage() . "\n";
    
    // Processar exceção manualmente
    $errorData = $handler->handle($exception);
    
    echo "Dados de erro formatados:\n";
    echo "Status: " . $errorData['status'] . "\n";
    echo "Trace ID: " . $errorData['trace-id'] . "\n";
    echo "Timestamp: " . $errorData['timestamp'] . "\n";
    
    // Em um ambiente real, você faria:
    // $handler->log($exception, $errorData);
    // $handler->sendResponse($errorData);
}

// Exemplo 3: Diferentes tipos de exceções
echo "\n=== Exemplo 3: Diferentes Tipos de Exceções ===\n";

$exceptions = [
    new ValidatorException('Dados inválidos'),
    new AuthenticationException('Usuário não autenticado'),
    new DomainException('Regra de negócio violada'),
    new InvalidArgumentException('Argumento inválido'),
    new RuntimeException('Erro de execução'),
];

foreach ($exceptions as $exception) {
    $errorData = $handler->handle($exception);
    echo sprintf(
        "%s: Status %d - %s\n",
        get_class($exception),
        $errorData['status'],
        $errorData['message']
    );
}

// Exemplo 4: Simulação de erro fatal
echo "\n=== Exemplo 4: Simulação de Erro Fatal ===\n";

try {
    // Simular um erro que seria fatal
    $error = [
        'type' => E_ERROR,
        'message' => 'Call to undefined function undefined_function()',
        'file' => '/app/example.php',
        'line' => 25
    ];
    
    $fatalException = new ErrorException(
        $error['message'],
        0,
        $error['type'],
        $error['file'],
        $error['line']
    );
    
    $errorData = $handler->handle($fatalException);
    echo "Erro fatal processado:\n";
    echo "Status: " . $errorData['status'] . "\n";
    echo "Mensagem: " . $errorData['message'] . "\n";
    
} catch (Throwable $e) {
    echo "Erro ao processar erro fatal: " . $e->getMessage() . "\n";
}

// Exemplo 5: Teste de logging (simulado)
echo "\n=== Exemplo 5: Teste de Logging ===\n";

$testException = new RuntimeException('Erro de teste para logging');

try {
    $errorData = $handler->handle($testException);
    $handler->log($testException, $errorData);
    echo "Logging realizado com sucesso\n";
} catch (Throwable $e) {
    echo "Erro no logging: " . $e->getMessage() . "\n";
}

// Função auxiliar para simular operação arriscada
function riskyOperation(): string
{
    $random = rand(1, 10);
    
    if ($random <= 3) {
        throw new RuntimeException('Operação falhou aleatoriamente');
    }
    
    if ($random <= 6) {
        throw new ValidatorException('Dados inválidos na operação');
    }
    
    return "Operação bem-sucedida (número: $random)";
}

echo "\n=== Fim dos Exemplos ===\n";
echo "O sistema de exceções está funcionando corretamente!\n"; 