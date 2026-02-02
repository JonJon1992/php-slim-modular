# Queue System - Guia de Uso

## 📋 Estrutura Modular

O sistema de queue agora é **modular**, permitindo múltiplos módulos com suas próprias conexões.

### Configuração (`configs/queue.php`)

```php
<?php

return [
    'default' => 'ms-keys',  // Módulo padrão

    'ms-keys' => [
        'connections' => [
            'sync' => [
                'driver' => 'sync',
            ],
            'database' => [
                'driver' => 'database',
                'table' => 'jobs',
                'queue' => 'default',
                'retry_after' => 90,
            ],
            'redis' => [
                'driver' => 'redis',
                'connection' => 'default',
                'queue' => 'default',
                'retry_after' => 90,
            ],
            'sqs' => [
                'driver' => 'sqs',
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'region' => env('AWS_REGION', 'us-east-1'),
                'queue' => env('SQS_QUEUE'),
            ],
        ],
        'failed' => [
            'table' => 'failed_jobs',
        ]
    ],
];
```

## 🎯 Uso

### Helper Function

```php
// Obter o QueueManager do módulo padrão (ms-keys)
$queueManager = queue();

// Especificar módulo
$queueManager = queue('ms-keys');

// Usar conexão específica
$queueManager->connection('database')->push(new MyJob());
$queueManager->connection('redis')->push(new MyJob());
$queueManager->connection('sqs')->push(new MyJob());
$queueManager->connection('sync')->push(new MyJob());

// Usar conexão padrão (primeira configurada, normalmente'sync')
$queueManager->push(new MyJob());
```

### Service Container

```php
// Acessar diretamente pelo service container
$queueManager = service('queue.ms-keys');

// Depois escolher a conexão
$queueManager->connection('database')->push(new MyJob());
$queueManager->connection('redis')->push(new MyJob());
```

### Queue Worker (CLI)

#### Usar módulo e conexão padrão

```bash
php artisan queue:work
```

#### Especificar módulo

```bash
php artisan queue:work --module=ms-keys
```

#### Especificar módulo e conexão

```bash
php artisan queue:work --module=ms-keys --connection=database
php artisan queue:work --module=ms-keys --connection=redis
php artisan queue:work --module=ms-keys --connection=sqs
```

#### Opções adicionais

```bash
php artisan queue:work \
    --module=ms-keys \
    --connection=database \
    --queue=notifications \
    --sleep=3 \
    --timeout=60 \
    --tries=3 \
    --memory=128
```

### Opções do Worker

| Opção          | Descrição                                   | Padrão                   |
| -------------- | ------------------------------------------- | ------------------------ |
| `--module`     | Módulo de queue a usar                      | Valor de `queue.default` |
| `--connection` | Conexão a usar (sync, database, redis, sqs) | `database`               |
| `--queue`      | Nome da fila                                | `default`                |
| `--sleep`      | Segundos de espera quando não há jobs       | `3`                      |
| `--timeout`    | Timeout do job em segundos                  | `60`                     |
| `--tries`      | Tentativas antes de falhar                  | `0` (ilimitado)          |
| `--memory`     | Limite de memória em MB                     | `128`                    |

## 📝 Exemplos

### Enviar Email via Queue

```php
use App\Jobs\SendEmailJob;

// Usando helper e conexão database
queue()->connection('database')->push(new SendEmailJob($user));

// Ou usar conexão padrão
queue()->push(new SendEmailJob($user));
```

### Processar Webhook

```php
use App\Jobs\ProcessWebhookJob;

// Usar conexão Redis para performance
queue()->connection('redis')->push(new ProcessWebhookJob($data));
```

### Enviar para SQS

```php
use App\Jobs\SyncDataJob;

// Usar SQS para processar em outra instância
queue()->connection('sqs')->push(new SyncDataJob($payload));
```

### Processamento Síncrono (Sync)

```php
use App\Jobs\QuickTaskJob;

// Processar imediatamente (útil para testes)
queue()->connection('sync')->push(new QuickTaskJob());
```

## 🔧 Criar Job

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MyJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $data
    ) {
    }

    public function handle(): void
    {
        // Lógica do job
        logger()->info('Processing job', $this->data);
    }
}
```

## 🚀 Múltiplos Módulos

Você pode ter vários módulos, cada um com suas próprias conexões:

```php
return [
    'default' => 'ms-keys',

    'ms-keys' => [
        'connections' => [...],
    ],

    'outro-modulo' => [
        'connections' => [
            'database' => [...],
            'redis' => [...],
        ],
    ],
];
```

Uso:

```php
queue('ms-keys', 'database')->push($job1);
queue('outro-modulo', 'redis')->push($job2);
```

## ⚠️ Importante

- O módulo padrão é definido em `queue.default`
- Se nenhum módulo for especificado, usa o padrão
- Se nenhuma conexão for especificada, usa `database`
- Certifique-se de que o módulo e conexão existem na configuração
