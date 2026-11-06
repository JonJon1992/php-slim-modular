<?php

declare(strict_types=1);

use Core\Traits\Services\{
    CacheServiceTrait,
    DatabaseServiceTrait,
    DynamoServiceTrait,
    LogServiceTrait,
    MailerServiceTrait,
    QueueServiceTrait,
    StorageServiceTrait,
    ViewServiceTrait
};
use Core\Utils\Config;
use DI\ContainerBuilder;

return new class {
    use LogServiceTrait;
    use DatabaseServiceTrait;
    use CacheServiceTrait;
    use StorageServiceTrait;
    use QueueServiceTrait;
    use DynamoServiceTrait;
    use ViewServiceTrait;
    use MailerServiceTrait;

    /**
     * @param ContainerBuilder $builder
     * @param array|null $modules instâncias já criadas dos módulos (evita instanciá-los de novo)
     * @throws Exception
     */
    public function __invoke(ContainerBuilder $builder, ?array $modules = null): void
    {
        $configs = Config::singleton();
        $bindings = [];
        try {
            $this->registerLogServices($bindings, $configs);
        } catch (\Throwable $e) {
            throw new \Exception("Log: " . $e->getMessage());
        }

        try {
            $this->registerDatabaseServices($bindings, $configs);
        } catch (\Throwable $e) {
            throw new \Exception("Database: " . $e->getMessage());
        }

        try {
            $this->registerCacheServices($bindings, $configs);
        } catch (\Throwable $e) {
            throw new \Exception("Cache: " . $e->getMessage());
        }

        try {
            $this->registerStorageServices($configs);
        } catch (\Throwable $e) {
            throw new \Exception("Storage: " . $e->getMessage());
        }

        try {
            $this->registerQueueServices($bindings, $configs);
        } catch (\Throwable $e) {
            throw new \Exception("Queue: " . $e->getMessage());
        }

        try {
            $this->registerDynamoServices($bindings, $configs);
        } catch (\Throwable $e) {
            throw new \Exception("Dynamo: " . $e->getMessage());
        }

        try {
            $this->registerViewServices($bindings, $configs, $modules);
        } catch (\Throwable $e) {
            throw new \Exception("View: " . $e->getMessage());
        }

        try {
            $this->registerMailerServices($bindings, $configs);
        } catch (\Throwable $e) {
            throw new \Exception("Mailer: " . $e->getMessage());
        }

        $builder->addDefinitions($bindings);
    }
};
