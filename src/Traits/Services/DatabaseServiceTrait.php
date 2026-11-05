<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Core\Utils\Env;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Events\Dispatcher;
use Psr\Container\ContainerInterface;

trait DatabaseServiceTrait
{
    protected function registerDatabaseServices(array &$bindings, $configs): void
    {
        $connections = array_filter(
            $configs->get('db', []),
            fn($settings, $alias) => $alias !== 'default' && is_array($settings),
            ARRAY_FILTER_USE_BOTH
        );

        if (empty($connections)) {
            return;
        }

        // Um único Manager com todas as conexões (nomeadas pelo alias). Antes cada alias tinha o
        // próprio Manager e cada bootEloquent() trocava o resolver global do Eloquent.
        $bindings['db.manager'] = function () use ($configs, $connections) {
            $manager = new Manager();
            foreach ($connections as $alias => $settings) {
                $manager->addConnection($settings, $alias);
            }
            $manager->setEventDispatcher(new Dispatcher(new Container()));

            // Models sem $connection usam o banco do módulo atual; fora de um módulo com
            // banco próprio, o db.default. Para outro banco: protected $connection = '<alias>'.
            $module = app()->module();
            $default = is_string($module) && isset($connections[$module])
                ? $module
                : ($configs->get('db.default') ?? array_key_first($connections));
            $manager->getDatabaseManager()->setDefaultConnection($default);

            $manager->setAsGlobal();
            $manager->bootEloquent();

            return $manager;
        };

        foreach (array_keys($connections) as $alias) {
            $bindings["db.$alias"] = function (ContainerInterface $container) use ($alias) {
                $connection = $container->get('db.manager')->getConnection($alias);

                if (!Env::prod() && \env('APP_DEBUG_SQL')) {
                    $connection->listen(
                        function (QueryExecuted $query) {
                            logger('sql-debug')
                                ->debug('Query executada', [
                                    'sql' => $query->sql,
                                    'bindings' => $query->bindings,
                                    'time' => $query->time
                                ]);
                        }
                    );
                }

                return $connection;
            };
        }
    }
}
