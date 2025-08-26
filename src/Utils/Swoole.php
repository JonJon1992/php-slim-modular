<?php
declare(strict_types=1);

namespace Core\Utils;

/**
 * Utilitário para gerenciamento do Swoole
 * 
 * Esta classe fornece métodos para detectar e configurar o Swoole,
 * permitindo que a aplicação funcione com ou sem a extensão.
 * 
 * @package Core\Utils
 * @version 2.9.5
 */
class Swoole
{
    private static ?bool $available = null;
    private static array $config = [];

    /**
     * Verifica se o Swoole está disponível
     */
    public static function isAvailable(): bool
    {
        if (self::$available === null) {
            self::$available = extension_loaded('swoole') && class_exists('Swoole\HTTP\Server');
        }
        return self::$available;
    }

    /**
     * Obtém a versão do Swoole
     */
    public static function getVersion(): ?string
    {
        if (!self::isAvailable()) {
            return null;
        }
        return SWOOLE_VERSION;
    }

    /**
     * Obtém informações do Swoole
     */
    public static function getInfo(): array
    {
        if (!self::isAvailable()) {
            return [
                'available' => false,
                'version' => null,
                'features' => [],
                'message' => 'Swoole não está disponível'
            ];
        }

        return [
            'available' => true,
            'version' => self::getVersion(),
            'features' => self::getFeatures(),
            'message' => 'Swoole está disponível'
        ];
    }

    /**
     * Obtém as funcionalidades disponíveis do Swoole
     */
    public static function getFeatures(): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        $features = [];

        // Verifica funcionalidades básicas
        if (defined('SWOOLE_SOCK_TCP')) {
            $features[] = 'tcp_socket';
        }
        if (defined('SWOOLE_SOCK_UDP')) {
            $features[] = 'udp_socket';
        }
        if (defined('SWOOLE_SOCK_UNIX_DGRAM')) {
            $features[] = 'unix_socket';
        }

        // Verifica funcionalidades avançadas
        if (class_exists('Swoole\HTTP\Server')) {
            $features[] = 'http_server';
        }
        if (class_exists('Swoole\WebSocket\Server')) {
            $features[] = 'websocket_server';
        }
        if (class_exists('Swoole\Coroutine')) {
            $features[] = 'coroutines';
        }
        if (class_exists('Swoole\Timer')) {
            $features[] = 'timer';
        }
        if (class_exists('Swoole\Process')) {
            $features[] = 'process';
        }

        return $features;
    }

    /**
     * Obtém configuração padrão do Swoole
     */
    public static function getDefaultConfig(): array
    {
        return [
            'host' => Env::get('SWOOLE_HOST', '0.0.0.0'),
            'port' => (int) Env::get('SWOOLE_PORT', 9501),
            'workers' => (int) Env::get('SWOOLE_WORKERS', 4),
            'max_request' => (int) Env::get('SWOOLE_MAX_REQUEST', 10000),
            'enable_coroutine' => true,
            'max_coroutine' => 3000,
            'log_level' => SWOOLE_LOG_INFO,
            'log_file' => Path::storage('logs/swoole.log'),
            'pid_file' => Path::storage('swoole.pid'),
            'daemonize' => false,
            'max_connection' => 10000,
            'heartbeat_check_interval' => 60,
            'heartbeat_idle_time' => 600,
            'buffer_output_size' => 2 * 1024 * 1024,
            'enable_reuse_port' => true,
            'enable_preemptive_scheduler' => true,
        ];
    }

    /**
     * Define configuração personalizada
     */
    public static function setConfig(array $config): void
    {
        self::$config = array_merge(self::getDefaultConfig(), $config);
    }

    /**
     * Obtém a configuração atual
     */
    public static function getConfig(): array
    {
        if (empty(self::$config)) {
            self::$config = self::getDefaultConfig();
        }
        return self::$config;
    }

    /**
     * Verifica se deve usar o Swoole baseado na configuração
     */
    public static function shouldUse(): bool
    {
        // Verifica se o Swoole está disponível
        if (!self::isAvailable()) {
            return false;
        }

        // Verifica se foi explicitamente habilitado
        if (Env::get('SWOOLE_ENABLED', 'false') === 'true') {
            return true;
        }

        // Verifica se está em modo de produção
        if (Env::get('APP_ENV') === 'production') {
            return true;
        }

        // Verifica se foi solicitado via argumento de linha de comando
        if (php_sapi_name() === 'cli' && in_array('--swoole', $GLOBALS['argv'] ?? [])) {
            return true;
        }

        return false;
    }

    /**
     * Cria um servidor HTTP Swoole
     */
    public static function createHttpServer(): ?\Swoole\HTTP\Server
    {
        if (!self::isAvailable()) {
            return null;
        }

        $config = self::getConfig();
        $server = new \Swoole\HTTP\Server($config['host'], $config['port']);
        
        // Remove configurações que não são válidas para o set()
        $serverConfig = $config;
        unset($serverConfig['host'], $serverConfig['port']);
        
        $server->set($serverConfig);
        
        return $server;
    }

    /**
     * Obtém estatísticas do servidor Swoole
     */
    public static function getStats(): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        try {
            $stats = \Swoole\Server::stats();
            return [
                'server' => $stats,
                'memory' => [
                    'usage' => memory_get_usage(true),
                    'peak' => memory_get_peak_usage(true),
                ],
                'coroutines' => \Swoole\Coroutine::stats(),
            ];
        } catch (\Throwable $e) {
            return [
                'error' => $e->getMessage(),
                'memory' => [
                    'usage' => memory_get_usage(true),
                    'peak' => memory_get_peak_usage(true),
                ],
            ];
        }
    }

    /**
     * Inicia o servidor Swoole
     */
    public static function startServer(callable $requestHandler): void
    {
        if (!self::isAvailable()) {
            throw new \RuntimeException('Swoole não está disponível');
        }

        $server = self::createHttpServer();
        if (!$server) {
            throw new \RuntimeException('Não foi possível criar o servidor Swoole');
        }

        // Configura eventos do servidor
        $server->on('start', function (\Swoole\HTTP\Server $server) {
            $config = self::getConfig();
            echo "🚀 Servidor Swoole iniciado em http://{$config['host']}:{$config['port']}\n";
            echo "📊 Workers: {$config['workers']}\n";
            echo "🔄 Max Request: {$config['max_request']}\n";
            echo "⏹️  Para parar: Ctrl+C\n";
        });

        $server->on('request', $requestHandler);

        $server->on('workerStart', function (\Swoole\HTTP\Server $server, int $workerId) {
            echo "👷 Worker {$workerId} iniciado (PID: " . posix_getpid() . ")\n";
        });

        $server->on('workerStop', function (\Swoole\HTTP\Server $server, int $workerId) {
            echo "🛑 Worker {$workerId} parado\n";
        });

        // Inicia o servidor
        $server->start();
    }

    /**
     * Obtém informações de diagnóstico
     */
    public static function getDiagnostics(): array
    {
        return [
            'swoole' => self::getInfo(),
            'environment' => [
                'php_version' => PHP_VERSION,
                'sapi' => php_sapi_name(),
                'extensions' => get_loaded_extensions(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
            ],
            'config' => self::getConfig(),
            'should_use' => self::shouldUse(),
        ];
    }
}
