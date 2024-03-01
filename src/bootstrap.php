<?php
declare(strict_types=1);

use Core\App\App;
use Core\Utils\Path;
use Core\Utils\Trace;
use DI\ContainerBuilder;
use Dotenv\Dotenv;

$global_started_at = microtime(true);

Trace::bootstrap($global_started_at);

Dotenv::createImmutable(Path::root())->load();


require 'exception.php';


$containerBuilder = new ContainerBuilder();
$bindings = require 'bindings.php';

foreach ($bindings as $key => $class) {
    $bindings[$key] = \DI\autowire($class);
}

$containerBuilder->addDefinitions($bindings);
$services = require 'services.php';
$services($containerBuilder);


$containerBuilder->addDefinitions(['modules' => array_map(function ($class) use ($containerBuilder) {
    $module = new $class;
    $containerBuilder->addDefinitions($module->bindings());
    $containerBuilder->addDefinitions([$class => $module]);
    return $module;
}, config('app.modules', []))]);


$app = App::singleton($containerBuilder->build());

$middlewares = require 'middlewares.php';
$middlewares($app);

$routes = require 'routes.php';
$routes($app);

$events = require 'events.php';
$events($app);