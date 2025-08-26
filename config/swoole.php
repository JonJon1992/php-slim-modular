<?php
declare(strict_types=1);

/**
 * Configuração do Swoole
 * 
 * Este arquivo contém todas as configurações disponíveis para o servidor Swoole.
 * As configurações podem ser sobrescritas através de variáveis de ambiente.
 * 
 * @package Core
 * @version 2.9.5
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações Básicas do Servidor
    |--------------------------------------------------------------------------
    |
    | Configurações fundamentais do servidor HTTP Swoole.
    |
    */
    'host' => env('SWOOLE_HOST', '0.0.0.0'),
    'port' => (int) env('SWOOLE_PORT', 9501),
    'workers' => (int) env('SWOOLE_WORKERS', 4),
    'max_request' => (int) env('SWOOLE_MAX_REQUEST', 10000),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Coroutines
    |--------------------------------------------------------------------------
    |
    | Configurações relacionadas ao sistema de coroutines do Swoole.
    |
    */
    'enable_coroutine' => env('SWOOLE_ENABLE_COROUTINE', true),
    'max_coroutine' => (int) env('SWOOLE_MAX_COROUTINE', 3000),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Log
    |--------------------------------------------------------------------------
    |
    | Configurações de logging do servidor Swoole.
    |
    */
    'log_level' => env('SWOOLE_LOG_LEVEL', SWOOLE_LOG_INFO),
    'log_file' => env('SWOOLE_LOG_FILE', storage_path('logs/swoole.log')),
    'pid_file' => env('SWOOLE_PID_FILE', storage_path('swoole.pid')),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Processo
    |--------------------------------------------------------------------------
    |
    | Configurações relacionadas ao processo do servidor.
    |
    */
    'daemonize' => env('SWOOLE_DAEMONIZE', false),
    'max_connection' => (int) env('SWOOLE_MAX_CONNECTION', 10000),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Heartbeat
    |--------------------------------------------------------------------------
    |
    | Configurações para detecção de conexões inativas.
    |
    */
    'heartbeat_check_interval' => (int) env('SWOOLE_HEARTBEAT_CHECK_INTERVAL', 60),
    'heartbeat_idle_time' => (int) env('SWOOLE_HEARTBEAT_IDLE_TIME', 600),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Buffer
    |--------------------------------------------------------------------------
    |
    | Configurações relacionadas ao buffer de saída.
    |
    */
    'buffer_output_size' => (int) env('SWOOLE_BUFFER_OUTPUT_SIZE', 2 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Performance
    |--------------------------------------------------------------------------
    |
    | Configurações para otimização de performance.
    |
    */
    'enable_reuse_port' => env('SWOOLE_ENABLE_REUSE_PORT', true),
    'enable_preemptive_scheduler' => env('SWOOLE_ENABLE_PREEMPTIVE_SCHEDULER', true),

    /*
    |--------------------------------------------------------------------------
    | Configurações de SSL/TLS
    |--------------------------------------------------------------------------
    |
    | Configurações para HTTPS (quando habilitado).
    |
    */
    'ssl_cert_file' => env('SWOOLE_SSL_CERT_FILE', ''),
    'ssl_key_file' => env('SWOOLE_SSL_KEY_FILE', ''),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Upload
    |--------------------------------------------------------------------------
    |
    | Configurações para upload de arquivos.
    |
    */
    'upload_tmp_dir' => env('SWOOLE_UPLOAD_TMP_DIR', '/tmp'),
    'max_package_length' => (int) env('SWOOLE_MAX_PACKAGE_LENGTH', 2 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Cache
    |--------------------------------------------------------------------------
    |
    | Configurações para cache interno do Swoole.
    |
    */
    'enable_static_handler' => env('SWOOLE_ENABLE_STATIC_HANDLER', false),
    'document_root' => env('SWOOLE_DOCUMENT_ROOT', public_path()),
    'enable_compression' => env('SWOOLE_ENABLE_COMPRESSION', true),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Segurança
    |--------------------------------------------------------------------------
    |
    | Configurações de segurança do servidor.
    |
    */
    'user' => env('SWOOLE_USER', ''),
    'group' => env('SWOOLE_GROUP', ''),
    'chroot' => env('SWOOLE_CHROOT', ''),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Monitoramento
    |--------------------------------------------------------------------------
    |
    | Configurações para monitoramento e métricas.
    |
    */
    'enable_stats' => env('SWOOLE_ENABLE_STATS', true),
    'stats_file' => env('SWOOLE_STATS_FILE', storage_path('swoole.stats')),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Desenvolvimento
    |--------------------------------------------------------------------------
    |
    | Configurações específicas para ambiente de desenvolvimento.
    |
    */
    'development' => [
        'enable_debug' => env('SWOOLE_DEBUG', false),
        'enable_trace' => env('SWOOLE_TRACE', false),
        'log_requests' => env('SWOOLE_LOG_REQUESTS', true),
        'log_errors' => env('SWOOLE_LOG_ERRORS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Produção
    |--------------------------------------------------------------------------
    |
    | Configurações específicas para ambiente de produção.
    |
    */
    'production' => [
        'enable_debug' => false,
        'enable_trace' => false,
        'log_requests' => env('SWOOLE_LOG_REQUESTS', false),
        'log_errors' => env('SWOOLE_LOG_ERRORS', true),
        'daemonize' => true,
        'workers' => (int) env('SWOOLE_WORKERS', 8),
        'max_request' => (int) env('SWOOLE_MAX_REQUEST', 10000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Ambiente
    |--------------------------------------------------------------------------
    |
    | Configurações que variam conforme o ambiente.
    |
    */
    'environments' => [
        'local' => [
            'workers' => 2,
            'max_request' => 1000,
            'enable_debug' => true,
            'log_requests' => true,
        ],
        'testing' => [
            'workers' => 1,
            'max_request' => 100,
            'enable_debug' => true,
            'log_requests' => true,
        ],
        'staging' => [
            'workers' => 4,
            'max_request' => 5000,
            'enable_debug' => false,
            'log_requests' => false,
        ],
        'production' => [
            'workers' => 8,
            'max_request' => 10000,
            'enable_debug' => false,
            'log_requests' => false,
            'daemonize' => true,
        ],
    ],
];
