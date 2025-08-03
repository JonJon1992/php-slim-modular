# Util DynamoDB

O util `Core\Utils\Dynamo` fornece uma interface simplificada para trabalhar com Amazon DynamoDB, encapsulando a package `jonjon1992/php-dynamo` e seguindo o padrão dos outros utils do projeto.

## Características

- **Configuração Automática**: Suporte automático para ambiente local (LocalStack) e produção (AWS)
- **Tratamento de Erros**: Exceções padronizadas com mensagens claras
- **Interface Fluente**: Métodos intuitivos para operações CRUD
- **Suporte a Transações**: Acesso ao gerenciador de transações
- **Hidratação Flexível**: Conversão para objetos, arrays ou stdClass
- **Operações em Lote**: Suporte para inserção e remoção em lote

## Instalação

O util já está incluído no projeto e depende da package `jonjon1992/php-dynamo` que já está configurada no `composer.json`.

## Configuração

### Ambiente Local (LocalStack)

```php
use Core\Utils\Dynamo;

// Configuração manual para LocalStack
$dynamo = Dynamo::createWithCredentials(
    'test',
    'test',
    'us-east-1',
    'http://localhost:4566'
);
```

### Ambiente de Produção (AWS)

```php
// Configuração automática usando variáveis de ambiente
$dynamo = Dynamo::create();

// Ou configuração manual
$dynamo = Dynamo::createWithCredentials(
    $_ENV['AWS_ACCESS_KEY_ID'],
    $_ENV['AWS_SECRET_ACCESS_KEY'],
    $_ENV['AWS_REGION']
);
```

### Configuração Customizada

```php
$config = [
    'key' => 'sua-access-key',
    'secret' => 'sua-secret-key',
    'region' => 'us-east-1',
    'endpoint' => 'http://localhost:4566' // opcional para LocalStack
];

$dynamo = Dynamo::create($config, 'App\\Models\\');
```

## Uso Básico

### 1. Definindo um Item

```php
<?php

use Jonjon\PhpDynamo\Item\DynamoItem;
use Jonjon\PhpDynamo\Item\DynamoProperty;

class User extends DynamoItem
{
    #[DynamoProperty(['name' => 'email', 'type' => 'string', 'required' => true])]
    private string $email;

    #[DynamoProperty(['name' => 'name', 'type' => 'string', 'required' => true])]
    private string $name;

    #[DynamoProperty(['name' => 'age', 'type' => 'integer', 'required' => false])]
    private ?int $age = null;

    public function pk(): string
    {
        return "USER#{$this->email}";
    }

    public function sk(): ?string
    {
        return "PROFILE#{$this->email}";
    }

    public static function getTableName(): string
    {
        return 'users';
    }

    // Getters e Setters...
}
```

### 2. Operações CRUD

#### Inserir Item

```php
$user = new User();
$user->setEmail('john@example.com')
     ->setName('John Doe')
     ->setAge(30);

$result = $dynamo->save($user);
```

#### Buscar Item

```php
$key = [
    'PK' => 'USER#john@example.com',
    'SK' => 'PROFILE#john@example.com'
];

$user = $dynamo->find('users', $key, User::class);
```

#### Atualizar Item

```php
$key = [
    'PK' => 'USER#john@example.com',
    'SK' => 'PROFILE#john@example.com'
];

$updateExpression = 'SET #age = :age, #updated_at = :updated_at';
$values = [
    ':age' => 31,
    ':updated_at' => date('Y-m-d H:i:s')
];

$result = $dynamo->update('users', $key, $updateExpression, $values);
```

#### Remover Item

```php
$user = new User();
$user->setEmail('john@example.com');

$result = $dynamo->delete($user);
```

### 3. Queries e Scans

#### Query

```php
$keyConditionExpression = 'PK = :pk';
$values = [
    ':pk' => 'USER#john@example.com'
];

$users = $dynamo->query('users', $keyConditionExpression, $values, null, null, null, [], User::class);
```

#### Scan

```php
$users = $dynamo->scan('users', null, null, [], User::class);
```

#### Query com Filtro

```php
$keyConditionExpression = 'PK = :pk';
$filterExpression = '#age > :min_age';
$values = [
    ':pk' => 'USER#john@example.com',
    ':min_age' => 25
];

$users = $dynamo->query('users', $keyConditionExpression, $values, $filterExpression, null, null, [], User::class);
```

### 4. Operações em Lote

#### Inserir Múltiplos Items

```php
$users = [
    new User('user1@example.com', 'User 1'),
    new User('user2@example.com', 'User 2'),
    new User('user3@example.com', 'User 3')
];

$result = $dynamo->insertItems($users);
```

#### Remover Múltiplos Items

```php
$users = [
    new User('user1@example.com'),
    new User('user2@example.com')
];

$result = $dynamo->deleteItems($users);
```

### 5. Gerenciamento de Tabelas

#### Listar Tabelas

```php
$tables = $dynamo->listTables();
```

#### Criar Tabela

```php
$tableSettings = [
    'TableName' => 'users',
    'KeySchema' => [
        [
            'AttributeName' => 'PK',
            'KeyType' => 'HASH'
        ],
        [
            'AttributeName' => 'SK',
            'KeyType' => 'RANGE'
        ]
    ],
    'AttributeDefinitions' => [
        [
            'AttributeName' => 'PK',
            'AttributeType' => 'S'
        ],
        [
            'AttributeName' => 'SK',
            'AttributeType' => 'S'
        ]
    ],
    'BillingMode' => 'PAY_PER_REQUEST'
];

$result = $dynamo->createTable($tableSettings);
```

#### Descrever Tabela

```php
$tableInfo = $dynamo->describeTable('users');
```

#### Remover Tabela

```php
$result = $dynamo->deleteTable('users');
```

### 6. Conversão de Dados

#### Converter Item

```php
// Para objeto
$user = $dynamo->convert($item, 'object', User::class);

// Para array
$userArray = $dynamo->convert($item, 'array');

// Para stdClass
$userStd = $dynamo->convert($item, 'stdclass');
```

#### Converter Múltiplos Items

```php
$users = $dynamo->convertItems($items, 'object', User::class);
```

### 7. Acesso Avançado

#### Cliente DynamoDB

```php
$client = $dynamo->getClient();
// Acesso direto ao cliente AWS SDK
```

#### Gerenciador de Transações

```php
$transaction = $dynamo->getTransaction();
// Acesso ao gerenciador de transações
```

#### DynamoManager

```php
$manager = $dynamo->getManager();
// Acesso direto ao DynamoManager
```

## Tratamento de Erros

O util captura exceções da package subjacente e as converte em exceções padronizadas com mensagens claras:

```php
try {
    $result = $dynamo->save($user);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
```

## Variáveis de Ambiente

O util suporta as seguintes variáveis de ambiente:

- `AWS_ACCESS_KEY_ID`: AWS Access Key
- `AWS_SECRET_ACCESS_KEY`: AWS Secret Key
- `AWS_REGION`: AWS Region
- `DYNAMODB_ENDPOINT`: Endpoint customizado (para LocalStack)

## Exemplo Completo

Veja o arquivo `examples/dynamo-example.php` para um exemplo completo de uso do util DynamoDB.

## Integração com o Framework

O util pode ser facilmente integrado com o sistema de injeção de dependência do framework:

```php
// Em um service provider
$container->set(Dynamo::class, function() {
    return Dynamo::create();
});

// Em um controller ou service
public function __construct(private Dynamo $dynamo) {}
```

## Boas Práticas

1. **Use Items Tipados**: Sempre defina classes que estendem `DynamoItem` para seus dados
2. **Configure PK/SK Adequadamente**: Use padrões consistentes para suas chaves
3. **Trate Erros**: Sempre envolva operações em try-catch
4. **Use Queries em vez de Scans**: Scans são mais custosos e lentos
5. **Configure Índices**: Use índices secundários para queries complexas
6. **Monitore Custos**: Use `PAY_PER_REQUEST` para desenvolvimento e `PROVISIONED` para produção 