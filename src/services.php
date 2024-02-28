<?php
declare(strict_types=1);

use Core\Utils\{Config, Redis};
use DI\ContainerBuilder;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Arr;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


return function (ContainerBuilder $builder) {
    $configs = Config::singleton();
    $bindings = [];

    foreach ($configs->get('log', []) as $alias => $settings) {
        if ($alias == 'default') {
            continue;
        }
        $bindings["log.$alias"] = function () use ($alias, $settings) {
            $path = Arr::get($settings, 'path');

            $handler = new StreamHandler($path, config('app.log_level'));
            $handler->setFormatter(new JsonFormatter());

            $logger = new Logger($alias);
            $logger->pushProcessor(function ($record) {
                return $record;
            });

            $logger->pushHandler($handler);
            return $logger;
        };
    }

    foreach ($configs->get('db', []) as $alias => $settings) {
        if ($alias == 'default') {
            continue;
        }
        $bindings["db.$alias"] = function () use ($configs, $alias, $settings) {
            $manager = new Manager();
            $manager->addConnection($settings);
            $manager->setEventDispatcher(new Dispatcher(new Container()));

            if ($alias == $configs->get('db.default')) {
                $manager->setAsGlobal();
            }
            $manager->bootEloquent();
            return $manager->getConnection();
        };
    }

    foreach ($configs->get('cache', []) as $alias => $settings) {
        if ($alias == 'default') {
            continue;
        }
        $bindings["cache.$alias"] = function () use ($settings) {
            return new Redis($settings);
        };
    }

    $builder->addDefinitions($bindings);
};