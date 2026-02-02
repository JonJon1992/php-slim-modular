<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Queue\Capsule\Manager as QueueManager;

trait QueueServiceTrait
{
    protected function registerQueueServices(array &$bindings, $configs): void
    {
        foreach ($configs->get('queue', []) as $module => $moduleSettings) {
            if ($module === 'default' || !isset($moduleSettings['connections'])) {
                continue;
            }

            foreach ($moduleSettings['connections'] as $alias => $settings) {
                $bindings["queue.$module.$alias"] = function () use ($configs, $module, $alias, $settings) {
                    $container = new Container();
                    $container->bind('db', function () {
                        return new class {
                            public function connection($name = null): Connection
                            {
                                return Manager::connection($name);
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
                    $capsule->addConnection($settings, $alias);

                    if ($module === $configs->get('queue.default')) {
                        $capsule->setAsGlobal();
                    }

                    return $capsule->getConnection($alias);
                };
            }
        }
    }
}
