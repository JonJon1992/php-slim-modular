<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Capsule\Manager as QueueCapsule;
use Illuminate\Queue\Connectors\DatabaseConnector;
use Illuminate\Queue\Connectors\SyncConnector;

trait QueueServiceTrait
{
    protected function registerQueueServices(array &$bindings, $configs): void
    {
        foreach ($configs->get('queue', []) as $module => $moduleSettings) {
            if ($module === 'default' || !isset($moduleSettings['connections'])) {
                continue;
            }

            $bindings["queue.$module"] = function () use ($configs, $module, $moduleSettings) {
                $defaultDbAlias = $configs->get('db.default', 'default');
                $dbResolver = new class($defaultDbAlias) implements ConnectionResolverInterface {
                    private string $defaultConnection;

                    public function __construct(string $defaultConnection)
                    {
                        $this->defaultConnection = $defaultConnection;
                    }

                    public function connection($name = null)
                    {
                        $alias = $name ?? $this->defaultConnection;
                        return service("db.{$alias}");
                    }

                    public function getDefaultConnection(): string
                    {
                        return $this->defaultConnection;
                    }

                    public function setDefaultConnection($name): void
                    {
                        $this->defaultConnection = $name;
                    }
                };

                $container = new Container();
                $container->instance(\Illuminate\Contracts\Container\Container::class, $container);

                $container->singleton('events', function ($c) {
                    return new Dispatcher($c);
                });


                $container->bind(\Illuminate\Contracts\Bus\Dispatcher::class, function ($c) {
                    return new \Illuminate\Bus\Dispatcher($c);
                });

                $container->alias(\Illuminate\Contracts\Bus\Dispatcher::class, 'bus');

                // Bind db
                $container->singleton('db', function () use ($dbResolver) {
                    return $dbResolver;
                });

                // Create the QueueCapsule
                $capsule = new QueueCapsule($container);

                // Add ALL connections from this module
                foreach ($moduleSettings['connections'] as $alias => $settings) {
                    $capsule->addConnection($settings, $alias);
                }

                // Get the internal manager and register our connectors
                $manager = $capsule->getQueueManager();

                $manager->addConnector('sync', function () {
                    return new SyncConnector;
                });

                $manager->addConnector('database', function () use ($dbResolver) {
                    return new DatabaseConnector($dbResolver);
                });

                if ($module === $configs->get('queue.default')) {
                    $capsule->setAsGlobal();
                }

                return $capsule;
            };
        }
    }
}
