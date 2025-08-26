<?php
declare(strict_types=1);

/**
 * Servidor Swoole para PHP Slim Modular Framework
 * 
 * Este arquivo permite executar a aplicação usando o Swoole como servidor web
 * de alta performance. O uso é opcional e requer a extensão Swoole instalada.
 * 
 * @package Core
 * @version 2.9.5
 */

use Core\Utils\Swoole;

/**
 * Inicializa e executa o servidor Swoole
 */
class SwooleServer
{
    private $app;
    private $config;

    public function __construct()
    {
        $this->loadApplication();
        $this->validateSwoole();
        $this->setupConfiguration();
    }

    /**
     * Carrega a aplicação Slim
     */
    private function loadApplication(): void
    {
        $this->app = require __DIR__ . '/../bootstrap.php';
    }

    /**
     * Valida se o Swoole está disponível
     */
    private function validateSwoole(): void
    {
        if (!Swoole::isAvailable()) {
            $this->showSwooleNotAvailable();
            exit(1);
        }

        if (!Swoole::shouldUse()) {
            $this->showSwooleNotEnabled();
            $this->runStandardServer();
            exit(0);
        }
    }

    /**
     * Configura o servidor Swoole
     */
    private function setupConfiguration(): void
    {
        $this->config = [
            'host' => $_ENV['SWOOLE_HOST'] ?? '0.0.0.0',
            'port' => (int) ($_ENV['SWOOLE_PORT'] ?? 9501),
            'workers' => (int) ($_ENV['SWOOLE_WORKERS'] ?? 4),
            'max_request' => (int) ($_ENV['SWOOLE_MAX_REQUEST'] ?? 10000),
            'enable_coroutine' => true,
            'log_level' => SWOOLE_LOG_INFO,
            'log_file' => __DIR__ . '/../storage/logs/swoole.log',
            'pid_file' => __DIR__ . '/../storage/swoole.pid',
            'daemonize' => false,
        ];

        Swoole::setConfig($this->config);
    }

    /**
     * Exibe mensagem quando Swoole não está disponível
     */
    private function showSwooleNotAvailable(): void
    {
        echo "❌ Erro: Extensão Swoole não está instalada.\n";
        echo "📦 Para instalar: pecl install swoole\n";
        echo "🔧 Alternativa: Use 'composer serve' para servidor padrão\n";
    }

    /**
     * Exibe mensagem quando Swoole não está habilitado
     */
    private function showSwooleNotEnabled(): void
    {
        echo "⚠️  Swoole não está habilitado\n";
        echo "🔧 Para habilitar: SWOOLE_ENABLED=true\n";
        echo "🚀 Usando servidor padrão do PHP...\n\n";
    }

    /**
     * Executa o servidor padrão do PHP
     */
    private function runStandardServer(): void
    {
        $this->app->run();
    }

    /**
     * Cria o handler de requisições
     */
    private function createRequestHandler(): callable
    {
        return function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            try {
                $this->processRequest($request, $response);
            } catch (Throwable $e) {
                $this->handleError($e, $response);
            }
        };
    }

    /**
     * Processa uma requisição HTTP
     */
    private function processRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response): void
    {
        // Converte a requisição Swoole para PSR-7
        $psrRequest = Swoole::createPsr7Request($request);
        
        // Processa a requisição através do Slim
        $psrResponse = $this->app->handle($psrRequest);
        
        // Converte a resposta PSR-7 para Swoole
        Swoole::createSwooleResponse($psrResponse, $response);
    }

    /**
     * Trata erros durante o processamento
     */
    private function handleError(Throwable $e, \Swoole\Http\Response $response): void
    {
        Swoole::handleError($e, $response);
        
        // Log do erro para debugging
        if ($_ENV['APP_DEBUG'] ?? false) {
            echo "❌ Erro: {$e->getMessage()}\n";
        }
    }

    /**
     * Exibe informações de inicialização
     */
    private function showStartupInfo(): void
    {
        echo "🚀 Servidor Swoole iniciado\n";
        echo "📍 Host: {$this->config['host']}\n";
        echo "🔌 Porta: {$this->config['port']}\n";
        echo "👷 Workers: {$this->config['workers']}\n";
        echo "🔄 Max Request: {$this->config['max_request']}\n";
        echo "⏹️  Para parar: Ctrl+C\n\n";
    }

    /**
     * Inicia o servidor Swoole
     */
    public function start(): void
    {
        $this->showStartupInfo();

        try {
            $requestHandler = $this->createRequestHandler();
            Swoole::startServer($requestHandler);
        } catch (Throwable $e) {
            echo "❌ Erro ao iniciar servidor: {$e->getMessage()}\n";
            exit(1);
        }
    }
}

// Executa o servidor
$server = new SwooleServer();
$server->start();
