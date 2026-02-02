<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Capsule\Manager as QueueCapsule;

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
                $container = new Container();

                // Bind events dispatcher
                $container->singleton('events', function () use ($container) {
                    return new Dispatcher($container);
                });

                // Bind db as ConnectionResolverInterface - required by DatabaseConnector
                $container->singleton('db', function () {
                    return new class implements ConnectionResolverInterface {
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
                });

                // Create capsule - it handles config internally
                $capsule = new QueueCapsule($container);

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
