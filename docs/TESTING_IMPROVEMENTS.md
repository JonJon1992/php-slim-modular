# Melhorias no BaseTestCase

## Visão Geral

O `BaseTestCase` foi aprimorado com melhorias significativas em segurança, robustez e funcionalidade para testes.

## Principais Melhorias Implementadas

### 1. **Segurança Aprimorada**

#### Problema Original
```php
exec("rm -f" . $arquivo);  // Falta espaço após -f
exec("mkdir -p" . $dir);   // Falta espaço após -p
```

#### Solução Implementada
```php
protected static function executeCommand(string $command, string $errorMessage): bool
{
    $output = [];
    $returnCode = 0;
    
    exec($command . ' 2>&1', $output, $returnCode);
    
    if ($returnCode !== 0) {
        $outputStr = implode("\n", $output);
        error_log("Comando falhou: {$command}");
        error_log("Saída: {$outputStr}");
        throw new \RuntimeException("{$errorMessage}: {$outputStr}");
    }
    
    return true;
}
```

**Benefícios:**
- ✅ Uso de `escapeshellarg()` para prevenir injection
- ✅ Captura de erros e logs detalhados
- ✅ Validação de códigos de retorno
- ✅ Mensagens de erro descritivas

### 2. **Permissões Configuráveis**

#### Antes
```php
exec("chown -R www-data:www-data " . $dir);
```

#### Depois
```php
$testUser = getenv('TEST_USER') ?: 'www-data';
static::executeCommand(
    "chown -R {$testUser}:{$testUser} " . escapeshellarg($dir),
    "Falha ao definir permissões do diretório"
);
```

**Benefícios:**
- ✅ Usuário configurável via variável de ambiente
- ✅ Fallback para www-data se não configurado
- ✅ Flexibilidade para diferentes ambientes

### 3. **Tratamento de Erros Robusto**

#### Melhorias Implementadas:
- **Try-Catch**: Envolvimento de operações críticas
- **Logs de Debug**: Rastreamento detalhado de operações
- **Validações**: Verificação de existência de arquivos/diretórios
- **Permissões Adequadas**: Definição de chmod apropriados

```php
try {
    // Operações de configuração
    error_log("Configurando SQLite para testes: {$arquivo}");
    
    if (file_exists($arquivo)) {
        static::executeCommand(
            "rm -f " . escapeshellarg($arquivo),
            "Falha ao remover arquivo SQLite existente"
        );
    }
    
    // ... outras operações
    
    error_log("SQLite configurado com sucesso: {$arquivo}");
    
} catch (\Exception $e) {
    error_log("Erro ao configurar SQLite: " . $e->getMessage());
    throw $e;
}
```

### 4. **Helpers para Testes**

#### `assertJsonResponse()`
```php
protected function assertJsonResponse(ResponseInterface $response, int $expectedStatusCode = 200): array
{
    $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
    
    $body = (string) $response->getBody();
    $data = json_decode($body, true);
    
    $this->assertNotFalse($data, 'Resposta deve ser JSON válido');
    
    return $data;
}
```

**Uso:**
```php
public function testCreateUser()
{
    $response = $this->post('/api/users', ['name' => 'João']);
    $data = $this->assertJsonResponse($response, 201);
    
    $this->assertEquals('João', $data['name']);
}
```

#### `createTestData()`
```php
protected function createTestData(array $overrides = []): array
{
    $defaultData = [
        'id' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    return array_merge($defaultData, $overrides);
}
```

**Uso:**
```php
$userData = $this->createTestData([
    'name' => 'Maria',
    'email' => 'maria@email.com'
]);
```

#### `cleanTestData()`
```php
protected function cleanTestData(string $table): void
{
    try {
        $container = self::getInstance()->getContainer();
        $db = $container->get('db.default');
        $db->exec("DELETE FROM {$table}");
    } catch (\Exception $e) {
        error_log("Erro ao limpar dados de teste: " . $e->getMessage());
    }
}
```

**Uso:**
```php
public function tearDown(): void
{
    $this->cleanTestData('users');
    parent::tearDown();
}
```

## Configuração de Ambiente

### Variáveis de Ambiente

```bash
# Usuário para permissões de arquivos (opcional)
export TEST_USER=www-data

# Para desenvolvimento local
export TEST_USER=$(whoami)
```

### Exemplo de Uso Completo

```php
class UserTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        static::configDbSqlite();
    }
    
    public function testCreateUser()
    {
        $userData = $this->createTestData([
            'name' => 'João Silva',
            'email' => 'joao@email.com'
        ]);
        
        $response = $this->post('/api/users', $userData);
        $data = $this->assertJsonResponse($response, 201);
        
        $this->assertEquals('João Silva', $data['name']);
        $this->assertEquals('joao@email.com', $data['email']);
        $this->assertArrayHasKey('id', $data);
    }
    
    public function testGetUser()
    {
        $response = $this->get('/api/users/1');
        $data = $this->assertJsonResponse($response, 200);
        
        $this->assertArrayHasKey('name', $data);
    }
    
    public function tearDown(): void
    {
        $this->cleanTestData('users');
        parent::tearDown();
    }
}
```

## Benefícios das Melhorias

### 1. **Segurança**
- Prevenção de command injection
- Validação de entradas
- Logs de auditoria

### 2. **Robustez**
- Tratamento de erros abrangente
- Validações de sucesso
- Fallbacks apropriados

### 3. **Flexibilidade**
- Configuração via ambiente
- Helpers reutilizáveis
- Código mais limpo

### 4. **Manutenibilidade**
- Logs detalhados
- Código bem documentado
- Separação de responsabilidades

### 5. **Produtividade**
- Helpers para tarefas comuns
- Menos código boilerplate
- Testes mais legíveis

## Próximos Passos

1. **Testes Unitários**: Criar testes para o próprio BaseTestCase
2. **Documentação**: Adicionar exemplos específicos por módulo
3. **CI/CD**: Integrar com pipeline de testes automatizados
4. **Monitoramento**: Adicionar métricas de performance dos testes

## Compatibilidade

- ✅ PHP 8.0+
- ✅ PHPUnit 9.0+
- ✅ Slim Framework 4.x
- ✅ Linux/Unix (para comandos shell)
- ⚠️ Windows (requer adaptações para comandos)

## Troubleshooting

### Erro de Permissão
```bash
# Verificar usuário atual
whoami

# Definir usuário correto
export TEST_USER=$(whoami)
```

### Erro de Comando
```bash
# Verificar logs
tail -f /var/log/php_errors.log

# Executar com debug
php -d display_errors=1 vendor/bin/phpunit
```

### Problemas de SQLite
```bash
# Verificar permissões
ls -la /path/to/sqlite/db/

# Recriar arquivo
rm -f /path/to/sqlite/db/test.db
touch /path/to/sqlite/db/test.db
chmod 644 /path/to/sqlite/db/test.db
```
