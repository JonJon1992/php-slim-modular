<?php

declare(strict_types=1);

/**
 * Bootstrap da Aplicação
 * 
 * Este arquivo é responsável por inicializar todos os componentes
 * necessários para o funcionamento da aplicação.
 * 
 * Suporta inicialização automática de servidor Swoole ou retorno
 * da aplicação para uso com servidor padrão.
 * 
 * @package Core
 * @version 2.9.6
 */

use Core\App\App;
use Core\Exceptions\Exception\AppException;
use Core\Utils\Path;
use Core\Utils\Trace;
use Core\Utils\Swoole;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use function DI\autowire;

// Carrega helpers globais
require_once __DIR__ . '/helpers.php';

/**
 * Inicializa o sistema de rastreamento
 */
Trace::bootstrap();

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

require 'exception.php';

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

/**
 * Função para inicializar servidor automaticamente
 * 
 * @param bool $autoStart Se deve iniciar o servidor automaticamente
 * @return App|void Retorna a aplicação se não iniciar servidor
 */
function bootstrap_app(bool $autoStart = false)
{
    global $app;
    
    if (!$autoStart) {
        return $app;
    }
    
    // Detecta se deve usar Swoole
    $useSwoole = Swoole::isAvailable() && Swoole::shouldUse();
    
    if ($useSwoole) {
        // Inicia servidor Swoole
        $config = [
            'host' => $_ENV['SWOOLE_HOST'] ?? '0.0.0.0',
            'port' => (int) ($_ENV['SWOOLE_PORT'] ?? 9501),
            'workers' => (int) ($_ENV['SWOOLE_WORKERS'] ?? 4),
            'max_request' => (int) ($_ENV['SWOOLE_MAX_REQUEST'] ?? 10000),
            'enable_coroutine' => true,
            'log_level' => SWOOLE_LOG_INFO,
            'log_file' => Path::root() . '/storage/logs/swoole.log',
            'pid_file' => Path::root() . '/storage/swoole.pid',
            'daemonize' => false,
        ];
        
        Swoole::setConfig($config);
        
        echo "🚀 Iniciando servidor Swoole...\n";
        echo "📍 Host: {$config['host']}\n";
        echo "🔌 Porta: {$config['port']}\n";
        echo "👷 Workers: {$config['workers']}\n";
        echo "⏹️  Para parar: Ctrl+C\n\n";
        
        // Cria handler de requisições
        $requestHandler = function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($app) {
            try {
                // Converte a requisição Swoole para PSR-7
                $psrRequest = Swoole::createPsr7Request($request);
                
                // Processa a requisição através do Slim
                $psrResponse = $app->handle($psrRequest);
                
                // Converte a resposta PSR-7 para Swoole
                Swoole::createSwooleResponse($psrResponse, $response);
            } catch (Throwable $e) {
                Swoole::handleError($e, $response);
                
                if ($_ENV['APP_DEBUG'] ?? false) {
                    echo "❌ Erro: {$e->getMessage()}\n";
                }
            }
        };
        
        Swoole::startServer($requestHandler);
    } else {
        // Inicia servidor padrão do PHP
        if (Swoole::isAvailable() && !Swoole::shouldUse()) {
            echo "⚠️  Swoole não está habilitado\n";
            echo "🔧 Para habilitar: SWOOLE_ENABLED=true\n";
            echo "🚀 Usando servidor padrão do PHP...\n\n";
        }
        
        $app->run();
    }
}

// Aplicação pronta para uso
return $app;