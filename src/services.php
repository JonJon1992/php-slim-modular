<?php
declare(strict_types=1);

use Core\Utils\{Arr, Config, Path, Redis, Render\SlimPhpRenderAdapter, SqsClient, Trace, Dynamo};
use DI\ContainerBuilder;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nette\Mail\SmtpMailer;
use S3\Lib\S3;
use Slim\Views\PhpRenderer;


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
            $logger->pushProcessor(fn($record) => Trace::processor($record));
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

    $default = $configs->get('storage.default', '');
    $storages = Arr::except($configs->get('storage', []), ['default', $default]);
    $storages = Arr::merge([$default => $configs->get("storage.$default", [])], $storages);

    foreach ($storages as $alias => $settings) {
        S3::singleton()->use($alias, $settings);
    }

    foreach ($configs->get('queue', []) as $alias => $settings) {
        if ($alias == 'default') {
            continue;
        }

        $bindings["queue.$alias"] = function () use ($settings) {
            return new SqsClient($settings);
        };
    }

    foreach ($configs->get('dynamo', []) as $alias => $settings) {
        if ($alias == 'default') {
            continue;
        }

        $bindings["dynamo.$alias"] = function () use ($settings) {
            return Dynamo::create($settings);
        };
    }

    $bindings['view'] = function () use ($configs) {
        return new PhpRenderer($configs->get('app.view.folder'));
    };

    foreach (config('app.modules', []) as $class) {

        $module = new $class();
        $alias = $module->namespace();
        $dir = Path::modules('/' . $module->namespace() . $module->viewsFolder());

        if (!is_dir($dir)) {
            continue;
        }
        
        $bindings["view.$alias"] = function () use ($dir) {
            return new SlimPhpRenderAdapter($dir);
        };
    }

    foreach ($configs->get('mailer', []) as $alias => $settings) {
        if ($alias == 'default') {
            continue;
        }

        $bindings["mailer.$alias"] = function () use ($settings, $configs, $alias) {
            return new SmtpMailer($settings);
        };
    }

    $builder->addDefinitions($bindings);
};