# Sistema de Tratamento de Exceções

## Visão Geral

O sistema de tratamento de exceções foi completamente refatorado seguindo as melhores práticas modernas de PHP e princípios SOLID. A nova implementação oferece melhor separação de responsabilidades, tipagem forte e maior flexibilidade.

## Arquitetura

### Estrutura de Arquivos

```
src/
├── exception.php                    # Configuração dos handlers globais
├── Exceptions/
│   ├── ExceptionHandler.php        # Classe principal de tratamento
│   ├── ErrorHandler.php            # Handler específico para Slim
│   └── [outras exceções...]
└── Contracts/
    └── Exception/
        └── IExceptionHandler.php   # Interface do handler
```

### Componentes Principais

#### 1. ExceptionHandler (src/Exceptions/ExceptionHandler.php)

Classe principal que implementa a interface `IExceptionHandler` e centraliza toda a lógica de tratamento de exceções.

**Responsabilidades:**
- Formatação de respostas de erro
- Logging estruturado
- Mapeamento de códigos de status HTTP
- Geração de informações de debug

**Métodos Principais:**
- `handle(Throwable $exception): array` - Processa exceção e retorna dados formatados
- `log(Throwable $exception, array $errorData): void` - Registra erro com contexto
- `sendResponse(array $errorData): void` - Envia resposta HTTP

#### 2. IExceptionHandler (src/Contracts/Exception/IExceptionHandler.php)

Interface que define o contrato para implementações de tratamento de exceções.

**Benefícios:**
- Facilita testes unitários
- Permite diferentes implementações
- Segue princípio de inversão de dependência

#### 3. Configuração Global (src/exception.php)

Arquivo de configuração que registra os handlers globais do PHP.

**Handlers Configurados:**
- `set_exception_handler()` - Exceções não capturadas
- `set_error_handler()` - Erros PHP convertidos para exceções
- `register_shutdown_function()` - Erros fatais

## Melhorias Implementadas

### 1. Tipagem Forte

```php
declare(strict_types=1);

public function handle(Throwable $exception): array
public function log(Throwable $exception, array $errorData): void
public function sendResponse(array $errorData): void
```

### 2. Separação de Responsabilidades

- **ExceptionHandler**: Lógica de negócio
- **Configuração**: Setup dos handlers globais
- **Interface**: Contrato para implementações

### 3. Logging Estruturado

```php
$context = [
    'trace_id' => $errorData['trace-id'],
    'status_code' => $errorData['status'],
    'exception_class' => get_class($exception),
    'request' => [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ],
    'user' => [
        'id' => auth()->id() ?? null,
    ],
    'stack_trace' => $exception->getTraceAsString(),
];
```

### 4. Mapeamento Inteligente de Status Codes

```php
private function getStatusCodeMap(): array
{
    return [
        'InvalidArgumentException' => 400,
        'DomainException' => 400,
        'RuntimeException' => 500,
        'Core\Exceptions\AppException' => 400,
        'Core\Exceptions\ValidatorException' => 422,
        'Core\Exceptions\AuthenticationException' => 401,
        // ... mais mapeamentos
    ];
}
```

### 5. Tratamento de Erros Fatais

```php
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
```

### 6. Fallback Seguro

```php
try {
    $logger = createLogger();
    $logger->log($logLevel, $exception->getMessage(), $context);
} catch (Throwable $logException) {
    // Fallback logging to prevent infinite loops
    error_log(sprintf(
        'Error logging failed: %s. Original error: %s',
        $logException->getMessage(),
        $exception->getMessage()
    ));
}
```

## Formato de Resposta

### Produção
```json
{
    "trace-id": "550e8400-e29b-41d4-a716-446655440000",
    "status": 400,
    "message": "Dados inválidos fornecidos",
    "timestamp": "2024-01-15T10:30:00+00:00",
    "path": "/api/users"
}
```

### Desenvolvimento
```json
{
    "trace-id": "550e8400-e29b-41d4-a716-446655440000",
    "status": 400,
    "message": "Dados inválidos fornecidos",
    "timestamp": "2024-01-15T10:30:00+00:00",
    "path": "/api/users",
    "debug": {
        "exception": "Core\\Exceptions\\ValidatorException",
        "file": "/app/src/Validators/UserValidator.php",
        "line": 45,
        "code": 422,
        "trace": "Stack trace...",
        "previous": null
    }
}
```

## Configuração

### Variáveis de Ambiente

```env
APP_ENV=development
APP_DEBUG=true
APP_LOG_LEVEL=error
```

### Configuração de Log

```php
// config/log.php
return [
    'main' => [
        'path' => storage_path('logs/app.log'),
    ],
];
```

## Uso

### Tratamento Automático

O sistema funciona automaticamente para exceções não capturadas:

```php
// Esta exceção será tratada automaticamente
throw new Core\Exceptions\ValidatorException('Dados inválidos');
```

### Uso Manual

```php
use Core\Exceptions\ExceptionHandler;

$handler = new ExceptionHandler();

try {
    // Código que pode gerar exceção
    riskyOperation();
} catch (Throwable $exception) {
    $errorData = $handler->handle($exception);
    $handler->log($exception, $errorData);
    $handler->sendResponse($errorData);
}
```

## Testes

### Teste da Interface

```php
use Core\Contracts\Exception\IExceptionHandler;

class MockExceptionHandler implements IExceptionHandler
{
    public function handle(Throwable $exception): array
    {
        return ['status' => 500, 'message' => 'Test error'];
    }
    
    public function log(Throwable $exception, array $errorData): void
    {
        // Mock implementation
    }
    
    public function sendResponse(array $errorData): void
    {
        // Mock implementation
    }
}
```

### Teste da Classe Principal

```php
use Core\Exceptions\ExceptionHandler;

$handler = new ExceptionHandler();
$exception = new InvalidArgumentException('Test exception');

$result = $handler->handle($exception);

$this->assertEquals(400, $result['status']);
$this->assertArrayHasKey('trace-id', $result);
$this->assertArrayHasKey('timestamp', $result);
```

## Benefícios

### 1. Manutenibilidade
- Código organizado e bem documentado
- Separação clara de responsabilidades
- Fácil de estender e modificar

### 2. Testabilidade
- Interface permite mocking
- Métodos pequenos e focados
- Dependências injetadas

### 3. Robustez
- Fallback para logging
- Tratamento de erros fatais
- Headers HTTP apropriados

### 4. Flexibilidade
- Diferentes implementações possíveis
- Configuração por ambiente
- Logging estruturado

### 5. Performance
- Instância única do handler
- Logging assíncrono quando possível
- Cache de configurações

## Próximos Passos

1. **Implementar logging assíncrono** para melhor performance
2. **Adicionar métricas** de erros e performance
3. **Criar dashboard** para monitoramento
4. **Implementar rate limiting** para logs
5. **Adicionar suporte a múltiplos formatos** de resposta

## Compatibilidade

- **PHP**: 8.2+
- **Slim**: 4.x
- **Monolog**: 3.x
- **PSR-3**: Compatível com interface de logging

## Contribuição

Para contribuir com melhorias no sistema de exceções:

1. Siga os padrões estabelecidos
2. Adicione testes para novas funcionalidades
3. Atualize a documentação
4. Mantenha compatibilidade com versões anteriores 