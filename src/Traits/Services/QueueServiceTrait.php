<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
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

            // Create ONE QueueCapsule per module with ALL connections
            $bindings["queue.$module"] = function () use ($configs, $module, $moduleSettings) {
                // Create a custom container with required bindings
                $container = new Container();

                // Bind events dispatcher
                $container->singleton('events', function ($c) {
                    return new Dispatcher($c);
                });

                // Create a proper ConnectionResolver for database queue
                $dbResolver = new class implements ConnectionResolverInterface {
                    public function connection($name = null)
                    {
                        return Manager::connection($name);
                    }

                    public function getDefaultConnection(): string
                    {
                        return 'default';
                    }

                    public function setDefaultConnection($name): void
                    {
                        // No-op
                    }
                };

                $container->singleton('db', function () use ($dbResolver) {
                    return $dbResolver;
                });

                // Create capsule
                $capsule = new QueueCapsule($container);

                // Get the internal QueueManager
                $manager = $capsule->getQueueManager();

                // Manually register connectors to avoid issues with provider
                $manager->addConnector('sync', function () {
                    return new SyncConnector;
                });

                $manager->addConnector('database', function () use ($dbResolver) {
                    return new DatabaseConnector($dbResolver);
                });

                // Add ALL connections from this module
                foreach ($moduleSettings['connections'] as $alias => $settings) {
                    $capsule->addConnection($settings, $alias);
                }

                if ($module === $configs->get('queue.default')) {
                    $capsule->setAsGlobal();
                }

                return $capsule;
            };
        }
    }
}
