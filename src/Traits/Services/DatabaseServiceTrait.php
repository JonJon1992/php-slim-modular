<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Core\Utils\Env;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Events\Dispatcher;

trait DatabaseServiceTrait
{
    protected function registerDatabaseServices(array &$bindings, $configs): void
    {
        foreach ($configs->get('db', []) as $alias => $settings) {
            if ($alias === 'default') {
                continue;
            }
            $bindings["db.$alias"] = function () use ($configs, $alias, $settings) {
                $manager = new Manager();
                $manager->addConnection($settings);
                $manager->setEventDispatcher(new Dispatcher(new Container()));

                if ($alias === $configs->get('db.default')) {
                    $manager->setAsGlobal();
                }
                if (!Env::prod() && \env('APP_DEBUG_SQL')) {
                    $manager->getConnection()->listen(
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
                $manager->bootEloquent();
                return $manager->getConnection();
            };
        }
    }
}
