# PHP Slim Modular Framework

[![Version](https://img.shields.io/badge/version-2.9.2-blue.svg)](https://github.com/JonJon1992/php-slim-modular)
[![PHP](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Um framework PHP modular baseado no Slim Framework com suporte completo para AWS, autenticação, criptografia e sistema de módulos.

## 🚀 Características

- **Framework Modular**: Sistema de módulos flexível e extensível
- **AWS Integration**: Suporte completo para S3, SQS e outros serviços AWS
- **Autenticação**: Sistema robusto de autenticação e autorização
- **Criptografia**: Criptografia de dados sensíveis
- **Migrações**: Sistema de migrações de banco de dados
- **Logging**: Sistema de logs estruturado
- **Cache**: Suporte para Redis e outros sistemas de cache
- **API Gateway**: Middleware para API Gateway
- **Health Check**: Endpoint de verificação de saúde dos serviços

## 📋 Requisitos

- PHP >= 8.2
- Composer
- Extensões PHP:
  - ext-fileinfo
  - ext-json
  - ext-openssl
  - ext-redis
  - ext-sodium

## 🛠️ Instalação

```bash
composer require jonjon1992/php-slim-modular:^2.9.2
```

## 🔧 Configuração

### AWS Configuration

```php
// config/storage.php
return [
    'default' => 's3',
    's3' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_S3_BUCKET'),
        'version' => 'latest'
    ]
];

// config/queue.php
return [
    'default' => 'sqs',
    'sqs' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'version' => 'latest',
        'queue_url' => env('AWS_SQS_QUEUE_URL')
    ]
];
```

### Database Configuration

```php
// config/db.php
return [
    'default' => 'mysql',
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST'),
        'database' => env('DB_DATABASE'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ]
];
```

## 🚀 Uso Básico

### Criando um Módulo

```php
<?php

namespace App\Modules\User;

use Core\App\Module;

class UserModule extends Module
{
    public function namespace(): string
    {
        return 'user';
    }

    public function routes(App $app): RouteGroupInterface
    {
        return $app->group('/api/v1/users', function (RouteGroupInterface $group) {
            $group->get('', UserListAction::class);
            $group->post('', UserCreateAction::class);
            $group->get('/{id}', UserShowAction::class);
            $group->put('/{id}', UserUpdateAction::class);
            $group->delete('/{id}', UserDeleteAction::class);
        });
    }
}
```

### Criando uma Action

```php
<?php

namespace App\Modules\User\Actions;

use Core\Abstract\AbstractAction;

class UserListAction extends AbstractAction
{
    protected function action(): array
    {
        $users = User::all();
        
        return [
            'data' => $users,
            'total' => $users->count()
        ];
    }
}
```

### Usando AWS S3

```php
// Upload de arquivo
$s3 = s3('default');
$result = $s3->saveS3('path/to/file.jpg', '/local/path/file.jpg');

// Gerar URL
$url = $s3->generateUrl('path/to/file.jpg');
```

### Usando AWS SQS

```php
// Enviar mensagem
$queue = queue('default');
$result = $queue->sendMessage([
    'QueueUrl' => 'https://sqs.region.amazonaws.com/account/queue-name',
    'MessageBody' => json_encode(['data' => 'message'])
]);
```

### Usando AWS DynamoDB

```php
// Configuração automática
$dynamo = Dynamo::create();

// Ou configuração manual
$dynamo = Dynamo::createWithCredentials(
    'your-access-key',
    'your-secret-key',
    'us-east-1'
);

// Salvar item
$user = new User();
$user->setEmail('john@example.com')->setName('John Doe');
$result = $dynamo->save($user);

// Buscar item
$user = $dynamo->find('users', ['PK' => 'USER#john@example.com', 'SK' => 'PROFILE#john@example.com'], User::class);

// Query
$users = $dynamo->query('users', 'PK = :pk', [':pk' => 'USER#john@example.com'], null, null, null, [], User::class);
```

## 🔧 Comandos Disponíveis

### Migrações

```bash
# Criar nova migração
php cli.php migrate:create user create_users_table

# Executar migrações
php cli.php migrate:run
```

## 📊 Health Check

O framework inclui um endpoint de health check avançado que verifica:

- Conexões de banco de dados (MySQL, PostgreSQL, etc.)
- Conexões de cache (Redis)
- Conexões de storage (S3)
- Conexões DynamoDB
- Métricas detalhadas de performance

### Uso Básico

```bash
# Verificação completa de todos os serviços
GET /im-alive

# Verificação específica de um serviço
GET /im-alive?service=cache.redis

# Forçar verificação completa (ignorando cache)
GET /im-alive?force=1
```

### Resposta de Exemplo

```json
{
    "cache.redis": {
        "status": "alive",
        "latency": 1.23,
        "checked_at": 1691234567,
        "details": "Redis connection successful"
    },
    "db.mysql": {
        "status": "alive",
        "latency": 5.67,
        "checked_at": 1691234567,
        "details": "Database connection successful",
        "driver": "mysql"
    },
    "dynamo.main": {
        "status": "alive",
        "latency": 8.90,
        "checked_at": 1691234567,
        "details": "DynamoDB connection successful",
        "tables_count": 5
    },
    "_metrics": {
        "total_services": 3,
        "dead_services": 0,
        "healthy_services": 3,
        "total_time": 15.80,
        "timestamp": 1691234567,
        "status": "healthy"
    }
}
```

### Status HTTP Codes

- **200**: Todos os serviços saudáveis
- **503**: Pelo menos um serviço com falha
- **500**: Erro interno na aplicação

### Funcionalidades

- **Cache Inteligente**: Evita verificações desnecessárias
- **Métricas Detalhadas**: Latência e status de cada serviço
- **Logs Estruturados**: Logs de sucesso e falha
- **Verificação Seletiva**: Verifica apenas serviços específicos
- **Suporte ao DynamoDB**: Inclui verificação de tabelas

## 🔒 Segurança

- Criptografia automática de dados sensíveis
- Middleware de autenticação
- Validação de entrada de dados
- Sanitização de parâmetros

## 📝 Logs

O framework utiliza Monolog para logging estruturado:

```php
// Log de erro
logger()->error('Erro na operação', ['context' => 'dados']);

// Log de informação
logger()->info('Operação realizada', ['user_id' => 123]);
```

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🆕 Changelog

Veja [CHANGELOG.md](CHANGELOG.md) para um histórico completo de mudanças.

## 🐛 Reportando Bugs

Se você encontrar um bug, por favor abra uma issue no GitHub com:

- Descrição detalhada do bug
- Passos para reproduzir
- Versão do PHP e dependências
- Stack trace (se aplicável)

## 📞 Suporte

Para suporte, entre em contato através das issues do GitHub ou envie um email para [seu-email@exemplo.com].

---

**Versão 2.4.0** - Atualizado em 05 de Agosto de 2025 