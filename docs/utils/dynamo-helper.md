# Helper DynamoDB

O helper `dynamo()` fornece uma forma simplificada de acessar o util DynamoDB, seguindo o mesmo padrão dos outros helpers do projeto como `redis()`, `s3()`, `db()`, etc.

## Características

- **Padrão Consistente**: Segue o mesmo padrão dos outros helpers do projeto
- **Configuração Flexível**: Suporte a múltiplas configurações via alias
- **Injeção de Dependência**: Integra com o sistema de DI do framework
- **Fácil Uso**: Interface simples e intuitiva

## Uso Básico

### Uso Padrão

```php
// Usa configuração padrão
$dynamo = dynamo();

// Operações básicas
$tables = $dynamo->listTables();
$result = $dynamo->save($item);
```

### Uso com Alias Específico

```php
// Usa configuração específica
$dynamoLocal = dynamo('local');
$dynamoProd = dynamo('production');
```

## Configuração

### Arquivo de Configuração

Para usar o helper com configurações específicas, adicione a configuração do DynamoDB:

```php
// config/dynamo.php ou similar
return [
    'default' => 'local',
    
    'local' => [
        'key' => 'test',
        'secret' => 'test',
        'region' => 'us-east-1',
        'endpoint' => 'http://localhost:4566'
    ],
    
    'production' => [
        'key' => $_ENV['AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
        'region' => $_ENV['AWS_REGION']
    ]
];
```

### Variáveis de Ambiente

O helper também suporta configuração automática via variáveis de ambiente:

```env
# Configuração AWS
AWS_ACCESS_KEY_ID=sua-access-key
AWS_SECRET_ACCESS_KEY=sua-secret-key
AWS_REGION=us-east-1

# Configuração LocalStack (opcional)
DYNAMODB_ENDPOINT=http://localhost:4566
```

## Exemplos de Uso

### 1. Operações CRUD

```php
// Criar e salvar um item
$user = new User();
$user->setEmail('john@example.com')
     ->setName('John Doe');

$dynamo = dynamo();
$result = $dynamo->save($user);

// Buscar um item
$key = [
    'PK' => 'USER#john@example.com',
    'SK' => 'PROFILE#john@example.com'
];

$foundUser = $dynamo->find('users', $key, User::class);
```

### 2. Queries e Scans

```php
$dynamo = dynamo();

// Query
$keyConditionExpression = 'PK = :pk';
$values = [':pk' => 'USER#john@example.com'];
$users = $dynamo->query('users', $keyConditionExpression, $values, null, null, null, [], User::class);

// Scan
$allUsers = $dynamo->scan('users', null, null, [], User::class);
```

### 3. Operações em Lote

```php
$dynamo = dynamo();

// Inserir múltiplos items
$users = [
    new User('user1@example.com', 'User 1'),
    new User('user2@example.com', 'User 2')
];

$result = $dynamo->insertItems($users);

// Remover múltiplos items
$result = $dynamo->deleteItems($users);
```

### 4. Gerenciamento de Tabelas

```php
$dynamo = dynamo();

// Listar tabelas
$tables = $dynamo->listTables();

// Criar tabela
$tableSettings = [
    'TableName' => 'users',
    'KeySchema' => [
        ['AttributeName' => 'PK', 'KeyType' => 'HASH'],
        ['AttributeName' => 'SK', 'KeyType' => 'RANGE']
    ],
    'AttributeDefinitions' => [
        ['AttributeName' => 'PK', 'AttributeType' => 'S'],
        ['AttributeName' => 'SK', 'AttributeType' => 'S']
    ],
    'BillingMode' => 'PAY_PER_REQUEST'
];

$result = $dynamo->createTable($tableSettings);
```

## Integração com o Framework

### Injeção de Dependência

O helper integra automaticamente com o sistema de injeção de dependência:

```php
class UserService
{
    public function __construct(private \Core\Traits\Dynamo $dynamo) {}
    
    public function createUser(string $email, string $name): User
    {
        $user = new User();
        $user->setEmail($email)->setName($name);
        
        $this->dynamo->save($user);
        return $user;
    }
}
```

### Service Provider

Para configurações customizadas, você pode registrar o serviço manualmente:

```php
// Em um service provider
$container->set('dynamo.custom', function() {
    return Dynamo::createWithCredentials(
        'custom-key',
        'custom-secret',
        'us-east-1',
        'http://custom-endpoint:4566'
    );
});
```

## Comparação com Uso Direto

### Usando o Helper (Recomendado)

```php
// Simples e consistente
$dynamo = dynamo();
$result = $dynamo->save($item);
```

### Usando a Classe Diretamente

```php
// Mais verboso
$dynamo = Dynamo::create();
$result = $dynamo->save($item);
```

## Vantagens do Helper

1. **Consistência**: Segue o mesmo padrão dos outros helpers
2. **Simplicidade**: Interface mais limpa e intuitiva
3. **Configuração**: Suporte automático a múltiplas configurações
4. **Testabilidade**: Fácil de mockar em testes
5. **Flexibilidade**: Suporte a alias para diferentes ambientes

## Tratamento de Erros

O helper mantém o mesmo tratamento de erros da classe base:

```php
try {
    $dynamo = dynamo();
    $result = $dynamo->save($user);
} catch (Exception $e) {
    // Tratamento de erro
    logger()->error('Erro ao salvar usuário: ' . $e->getMessage());
}
```

## Boas Práticas

1. **Use o Helper**: Prefira `dynamo()` em vez de instanciar diretamente
2. **Configure Alias**: Use alias para diferentes ambientes
3. **Trate Erros**: Sempre envolva operações em try-catch
4. **Use Tipagem**: Defina classes que estendem `DynamoItem`
5. **Monitore**: Use logs para monitorar operações

## Exemplo Completo

Veja o arquivo `examples/dynamo-example.php` para um exemplo completo de uso do helper `dynamo()`. 