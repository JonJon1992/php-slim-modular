<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\ConnectionResolverInterface;
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

                // Bind db as ConnectionResolverInterface
                $container->singleton('db', function () {
                    return new class implements ConnectionResolverInterface {
                        public function connection($name = null): Connection
                        {
                            return Manager::connection($name);
                        }

                        public function getDefaultConnection(): string
                        {
                            return Manager::getDefaultConnection();
                        }

                        public function setDefaultConnection($name): void
                        {
                            Manager::setDefaultConnection($name);
                        }
                    };
                });

                $container->singleton('exception.handler', function () {
                    return new class {
                        public function report(\Throwable $e): void
                        {
                            if (function_exists('logger')) {
                                logger()->error($e->getMessage(), ['exception' => $e]);
                            }
                        }
                        public function shouldReport(\Throwable $e)
                        {
                            return true;
                        }
                    };
                });

                // Bind config for queue
                $container->singleton('config', function () use ($moduleSettings) {
                    return new class($moduleSettings) {
                        private array $config;

                        public function __construct(array $config)
                        {
                            $this->config = $config;
                        }

                        public function get($key, $default = null)
                        {
                            if ($key === 'queue.default') {
                                return 'database';
                            }
                            if ($key === 'queue.connections') {
                                return $this->config['connections'] ?? [];
                            }
                            return $default;
                        }
                    };
                });

                $capsule = new QueueCapsule($container);

                // Add ALL connections from this module
                foreach ($moduleSettings['connections'] as $alias => $settings) {
                    $capsule->addConnection($settings, $alias);
                }

                // Add 'default' connection pointing to 'database'
                if (isset($moduleSettings['connections']['database'])) {
                    $capsule->addConnection($moduleSettings['connections']['database'], 'default');
                }

                if ($module === $configs->get('queue.default')) {
                    $capsule->setAsGlobal();
                }

                return $capsule;
            };
        }
    }
}
