<?php

declare(strict_types=1);

use Core\App\App;
use Core\Exceptions\Exception\AppException;
use Core\Utils\Env;
use Core\Utils\Path;
use Core\Utils\Trace;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use function DI\autowire;

require_once __DIR__ . '/helpers.php';

Trace::bootstrap();

try {
    // safeLoad: o .env é opcional (em containers as variáveis já vêm do ambiente);
    // um .env presente mas inválido continua gerando erro.
    $dotenv = Dotenv::createImmutable(Path::root());
    $dotenv->safeLoad();
} catch (\Exception $e) {
    throw new AppException('Erro ao carregar variáveis de ambiente: ' . $e->getMessage(), 500, $e);
}

require 'exception.php';
require 'cors.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->useAttributes(true);

// Em production/staging, guarda no APCu as definições de autowiring para não refazer a
// reflexão a cada requisição. O namespace usa o caminho real do projeto, então cada release
// tem o próprio cache. Desative com APP_CONTAINER_CACHE=false.
if (Env::real() && env('APP_CONTAINER_CACHE', true) && function_exists('apcu_enabled') && apcu_enabled()) {
    $containerBuilder->enableDefinitionCache('php-slim-modular.' . md5(Path::root()));
}

$bindings = require 'bindings.php';
$processedBindings = [];

foreach ($bindings as $key => $class) {
    if (!class_exists($class)) {
        throw new AppException("Classe não encontrada para binding '{$key}': {$class}", 500);
    }
    $processedBindings[$key] = autowire($class);
}

$containerBuilder->addDefinitions($processedBindings);

// Cada módulo é instanciado uma única vez e compartilhado com os serviços (views).
$moduleClasses = config('app.modules', []);
$modules = array_map(fn(string $class) => new $class(), $moduleClasses);

$services = require 'services.php';
$services($containerBuilder, $modules);

foreach ($modules as $i => $module) {
    $containerBuilder->addDefinitions($module->bindings());
    $containerBuilder->addDefinitions([$moduleClasses[$i] => $module]);
}
$containerBuilder->addDefinitions(['modules' => $modules]);

try {
    $container = $containerBuilder->build();
    $app = App::singleton($container);

    $middlewares = require 'middlewares.php';
    $middlewares($app);

    $events = require 'events.php';
    $events($app);

    $routes = require 'routes.php';
    $routes($app);
} catch (\Exception $e) {
    throw new AppException('Erro ao inicializar aplicação: ' . $e->getMessage(), 500, $e);
}


