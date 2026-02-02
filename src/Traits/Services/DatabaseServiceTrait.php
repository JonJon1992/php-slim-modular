<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
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
                $manager->bootEloquent();
                return $manager->getConnection();
            };
        }
    }
}
