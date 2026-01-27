<?php

declare(strict_types=1);

/**
 * Bootstrap da Aplicação
 * 
 * Este arquivo é responsável por inicializar todos os componentes
 * necessários para o funcionamento da aplicação.
 * 
 * @package Core
 * @version 2.3.0
 */

use Core\App\App;
use Core\Exceptions\Exception\AppException;
use Core\Utils\Path;
use Core\Utils\Trace;
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
    if (!is_array($modules)) {
        throw new AppException('Configuração de módulos deve ser um array');
    }

    $moduleInstances = [];
    foreach ($modules as $moduleClass) {
        if (!class_exists($moduleClass)) {
            throw new AppException("Classe de módulo não encontrada: {$moduleClass}");
        }

        $module = new $moduleClass();

        $moduleBindings = $module->bindings();
        if (is_array($moduleBindings)) {
            $containerBuilder->addDefinitions($moduleBindings);
        }

        $containerBuilder->addDefinitions([$moduleClass => $module]);
        $moduleInstances[] = $module;
    }

    $containerBuilder->addDefinitions(['modules' => $moduleInstances]);
    $container = $containerBuilder->build();
} catch (\Exception $e) {
    throw new AppException('Erro ao configurar container de dependências: ' . $e->getMessage(), 0, $e);
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

    if (!$container->has('modules')) {
        throw new AppException('Container não possui módulos configurados');
    }
} catch (\Exception $e) {
    throw new AppException('Erro na validação final da aplicação: ' . $e->getMessage(), 0, $e);
}

return $app;
