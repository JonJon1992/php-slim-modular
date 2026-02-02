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

    public function __invoke(ContainerBuilder $builder): void
    {
        $configs = Config::singleton();
        $bindings = [];
        $this->registerLogServices($bindings, $configs);
        $this->registerDatabaseServices($bindings, $configs);
        $this->registerCacheServices($bindings, $configs);
        $this->registerStorageServices($configs);
        $this->registerQueueServices($bindings, $configs);
        $this->registerDynamoServices($bindings, $configs);
        $this->registerViewServices($bindings, $configs);
        $this->registerMailerServices($bindings, $configs);

        $builder->addDefinitions($bindings);
    }
};
