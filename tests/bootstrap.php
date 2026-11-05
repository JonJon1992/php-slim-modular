<?php

declare(strict_types=1);

use Core\App\App;
use Core\Traits\Services\DatabaseServiceTrait;
use Core\Traits\Services\QueueServiceTrait;
use Core\Utils\Trace;
use DI\ContainerBuilder;
use Tests\Support\ArrayConfig;

require __DIR__ . '/../vendor/autoload.php';

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'testing';
Trace::bootstrap();

// Mesma montagem de serviços do framework (traits de banco e fila), com dois bancos SQLite
// em memória e uma fila com as conexões sync e database.
$config = new ArrayConfig([
    'db' => [
        'default' => 'core',
        'core' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        'billing' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
    ],
    'queue' => [
        'default' => 'jobs',
        'jobs' => [
            'connections' => [
                'sync' => ['driver' => 'sync'],
                'database' => [
                    'driver' => 'database',
                    'connection' => 'core',
                    'table' => 'jobs',
                    'queue' => 'default',
                    'retry_after' => 60,
                ],
            ],
        ],
    ],
]);

$registrar = new class {
    use DatabaseServiceTrait;
    use QueueServiceTrait;

    public function register(array &$bindings, ArrayConfig $config): void
    {
        $this->registerDatabaseServices($bindings, $config);
        $this->registerQueueServices($bindings, $config);
    }
};

$bindings = [];
$registrar->register($bindings, $config);

$builder = new ContainerBuilder();
$builder->addDefinitions($bindings);
App::singleton($builder->build());
