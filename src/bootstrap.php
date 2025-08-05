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

// Carrega helpers globais
require_once __DIR__ . '/helpers.php';

/**
 * Inicializa o sistema de rastreamento
 */
Trace::bootstrap();

require 'exception.php';

/**
 * Carrega variáveis de ambiente
 */
try {
    $dotenv = Dotenv::createImmutable(Path::root());
    $dotenv->load();
    
    // Valida variáveis de ambiente obrigatórias
    $dotenv->required(['APP_ENV', 'APP_DEBUG'])->notEmpty();
} catch (\Exception $e) {
    throw new AppException('Erro ao carregar variáveis de ambiente: ' . $e->getMessage(), 0, $e);
}


/**
 * Configura o container de injeção de dependência
 * 
 * PHP-DI 7.0+ com suporte a atributos habilitado
 */
try {
    $containerBuilder = new ContainerBuilder();
    
    // Configurações do container
    $containerBuilder->useAutowiring(true);
    
    // Habilita atributos para PHP-DI 7.0+
    $containerBuilder->useAttributes(true);
    
    // Adiciona definições de bindings
    $bindings = require 'bindings.php';
    $processedBindings = [];
    
    foreach ($bindings as $key => $class) {
        if (!class_exists($class)) {
            throw new AppException("Classe não encontrada para binding '{$key}': {$class}");
        }
        $processedBindings[$key] = autowire($class);
    }
    
    $containerBuilder->addDefinitions($processedBindings);
    
    // Adiciona serviços
    $services = require 'services.php';
    $services($containerBuilder);
    
    // Configura módulos
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
        
        // Adiciona bindings do módulo
        $moduleBindings = $module->bindings();
        if (is_array($moduleBindings)) {
            $containerBuilder->addDefinitions($moduleBindings);
        }
        
        // Registra o módulo
        $containerBuilder->addDefinitions([$moduleClass => $module]);
        $moduleInstances[] = $module;
    }
    
    $containerBuilder->addDefinitions(['modules' => $moduleInstances]);
    
    // Constrói o container
    $container = $containerBuilder->build();
    
} catch (\Exception $e) {
    throw new AppException('Erro ao configurar container de dependências: ' . $e->getMessage(), 0, $e);
}

/**
 * Inicializa a aplicação
 */
try {
    $app = App::singleton($container);
    
    // Configura middlewares
    $middlewares = require 'middlewares.php';
    $middlewares($app);
    
    // Configura rotas
    $routes = require 'routes.php';
    $routes($app);
    
    // Configura eventos
    $events = require 'events.php';
    $events($app);
    
} catch (\Exception $e) {
    throw new AppException('Erro ao inicializar aplicação: ' . $e->getMessage(), 0, $e);
}

/**
 * Validações finais da aplicação
 */
try {
    // Verifica se a aplicação foi inicializada corretamente
    if (!$app instanceof App) {
        throw new AppException('Aplicação não foi inicializada corretamente');
    }
    
    // Verifica se o container está funcionando
    if (!$container->has('modules')) {
        throw new AppException('Container não possui módulos configurados');
    }
    
} catch (\Exception $e) {
    throw new AppException('Erro na validação final da aplicação: ' . $e->getMessage(), 0, $e);
}

// Aplicação pronta para uso
return $app;