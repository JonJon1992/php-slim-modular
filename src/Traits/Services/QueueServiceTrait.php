<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Queue\Capsule\Manager as QueueManager;

trait QueueServiceTrait
{
    protected function registerQueueServices(array &$bindings, $configs): void
    {
        foreach ($configs->get('queue', []) as $module => $moduleSettings) {
            if ($module === 'default' || !isset($moduleSettings['connections'])) {
                continue;
            }

            $bindings["queue.$module"] = function () use ($configs, $module, $moduleSettings) {
                $container = new Container();
                $container->bind('db', function () {
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

                $container->bind('exception.handler', function () {
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

                $capsule = new QueueManager($container);
                foreach ($moduleSettings['connections'] as $alias => $settings) {
                    $capsule->addConnection($settings, $alias);
                }
                if (isset($moduleSettings['connections']['database'])) {
                    $capsule->addConnection($moduleSettings['connections']['database'], 'default');
                } elseif (!empty($moduleSettings['connections'])) {
                    $firstKey = array_key_first($moduleSettings['connections']);
                    $capsule->addConnection($moduleSettings['connections'][$firstKey], 'default');
                }

                if ($module === $configs->get('queue.default')) {
                    $capsule->setAsGlobal();
                }

                return $capsule;
            };
        }
    }
}
