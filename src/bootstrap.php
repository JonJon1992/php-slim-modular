<?php

declare(strict_types=1);

use Core\App\App;
use Core\Exceptions\Exception\AppException;
use Core\Traits\Path;
use Core\Traits\Trace;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use function DI\autowire;

require_once __DIR__ . '/helpers.php';

Trace::bootstrap();

try {
    $dotenv = Dotenv::createImmutable(Path::root());
    $dotenv->load();

    $dotenv->required(['APP_ENV', 'APP_DEBUG'])->notEmpty();
} catch (\Exception $e) {
    throw new AppException('Erro ao carregar variáveis de ambiente: ' . $e->getMessage(), 0, $e);
}

require 'exception.php';
require 'cors.php';

try {
    $containerBuilder = new ContainerBuilder();
    $containerBuilder->useAutowiring(true);
    $containerBuilder->useAttributes(true);

    $bindings = require 'bindings.php';
    $processedBindings = [];

    foreach ($bindings as $key => $class) {
        if (!class_exists($class)) {
            throw new AppException("Classe não encontrada para binding '{$key}': {$class}");
        }
        $processedBindings[$key] = autowire($class);
    }

    $containerBuilder->addDefinitions($processedBindings);

    $services = require 'services.php';
    $services($containerBuilder);

    $modules = config('app.modules', []);
    $containerBuilder->addDefinitions(['modules' => array_map(function ($class) use ($containerBuilder) {
        $module = new $class();
        $containerBuilder->addDefinitions($module->bindings());
        $containerBuilder->addDefinitions([$class => $module]);
        return $module;
    }, $modules)]);
    $container = $containerBuilder->build();
} catch (\Exception $e) {
    throw new AppException('Erro ao configurar as dependências: ' . $e->getMessage(), 0, $e);
}

try {
    $app = App::singleton($container);

    $middlewares = require 'middlewares.php';
    $middlewares($app);

    $routes = require 'routes.php';
    $routes($app);

    $events = require 'events.php';
    $events($app);
} catch (\Exception $e) {
    throw new AppException('Erro ao inicializar aplicação: ' . $e->getMessage(), 0, $e);
}

try {
    if (!$app instanceof App) {
        throw new AppException('Aplicação não foi inicializada corretamente');
    }
    $app->run();
    if (!$container->has('modules')) {
        throw new AppException('Não foram encontrados módulos configurados');
    }
} catch (\Exception $e) {
    throw new AppException('Erro na validação da aplicação: ' . $e->getMessage(), 0, $e);
}

return $app;
