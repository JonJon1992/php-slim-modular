<?php

declare(strict_types=1);

/**
 * Exemplo de uso do Health Check melhorado
 * 
 * Este exemplo demonstra como usar o endpoint /im-alive com as novas funcionalidades:
 * - Verificação de todos os serviços
 * - Verificação específica por serviço
 * - Uso de cache
 * - Forçar verificação completa
 * 
 * @package Examples
 * @author JonJon1992
 * @version 2.3.0
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Core\App\App;
use Core\Utils\Config;

// Inicializa a aplicação
$app = new App();

echo "=== Health Check Examples ===\n\n";

// Exemplo 1: Verificação completa de todos os serviços
echo "1. Verificação completa de todos os serviços:\n";
echo "GET /im-alive\n";
echo "Resposta esperada:\n";
echo json_encode([
    'cache.redis' => [
        'status' => 'alive',
        'latency' => 1.23,
        'checked_at' => time(),
        'details' => 'Redis connection successful'
    ],
    'db.mysql' => [
        'status' => 'alive',
        'latency' => 5.67,
        'checked_at' => time(),
        'details' => 'Database connection successful',
        'driver' => 'mysql'
    ],
    'storage.s3' => [
        'status' => 'alive',
        'latency' => 12.34,
        'checked_at' => time(),
        'details' => 'S3 bucket accessible'
    ],
    'dynamo.main' => [
        'status' => 'alive',
        'latency' => 8.90,
        'checked_at' => time(),
        'details' => 'DynamoDB connection successful',
        'tables_count' => 5
    ],
    '_metrics' => [
        'total_services' => 4,
        'dead_services' => 0,
        'healthy_services' => 4,
        'total_time' => 28.14,
        'timestamp' => time(),
        'status' => 'healthy'
    ]
], JSON_PRETTY_PRINT);
echo "\n\n";

// Exemplo 2: Verificação específica de um serviço
echo "2. Verificação específica do Redis:\n";
echo "GET /im-alive?service=cache.redis\n";
echo "Resposta esperada:\n";
echo json_encode([
    'cache.redis' => [
        'status' => 'alive',
        'latency' => 1.23,
        'checked_at' => time(),
        'details' => 'Redis connection successful'
    ],
    '_metrics' => [
        'total_services' => 1,
        'dead_services' => 0,
        'healthy_services' => 1,
        'total_time' => 1.23,
        'timestamp' => time(),
        'status' => 'healthy'
    ]
], JSON_PRETTY_PRINT);
echo "\n\n";

// Exemplo 3: Verificação com cache
echo "3. Verificação com cache (primeira chamada):\n";
echo "GET /im-alive\n";
echo "Resposta: Verificação completa executada\n\n";

echo "4. Verificação com cache (segunda chamada em 30s):\n";
echo "GET /im-alive\n";
echo "Resposta esperada:\n";
echo json_encode([
    'status' => 'OK',
    'cached' => true,
    'timestamp' => time(),
    'message' => 'Using cached health check result'
], JSON_PRETTY_PRINT);
echo "\n\n";

// Exemplo 4: Forçar verificação completa
echo "5. Forçar verificação completa (ignorando cache):\n";
echo "GET /im-alive?force=1\n";
echo "Resposta: Verificação completa executada mesmo com cache disponível\n\n";

// Exemplo 5: Serviço com falha
echo "6. Serviço com falha:\n";
echo "GET /im-alive?service=db.mysql\n";
echo "Resposta esperada (se MySQL estiver inacessível):\n";
echo json_encode([
    'db.mysql' => [
        'status' => 'dead',
        'latency' => 5000.00,
        'checked_at' => time(),
        'error' => 'SQLSTATE[HY000] [2002] Connection refused',
        'details' => 'Database connection failed'
    ],
    '_metrics' => [
        'total_services' => 1,
        'dead_services' => 1,
        'healthy_services' => 0,
        'total_time' => 5000.00,
        'timestamp' => time(),
        'status' => 'unhealthy'
    ]
], JSON_PRETTY_PRINT);
echo "\n\n";

// Exemplo 6: Status HTTP codes
echo "7. Status HTTP codes:\n";
echo "- 200: Todos os serviços saudáveis\n";
echo "- 503: Pelo menos um serviço com falha\n";
echo "- 500: Erro interno na aplicação\n\n";

// Exemplo 7: Configuração necessária
echo "8. Configuração necessária:\n";
echo "Para que o health check funcione corretamente, você precisa configurar:\n\n";

echo "config/cache.php:\n";
echo json_encode([
    'default' => 'redis',
    'redis' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'port' => env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD'),
        'database' => env('REDIS_DB', 0)
    ]
], JSON_PRETTY_PRINT);
echo "\n\n";

echo "config/db.php:\n";
echo json_encode([
    'default' => 'mysql',
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci'
    ]
], JSON_PRETTY_PRINT);
echo "\n\n";

echo "config/storage.php:\n";
echo json_encode([
    'default' => 's3',
    's3' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_S3_BUCKET'),
        'version' => 'latest'
    ]
], JSON_PRETTY_PRINT);
echo "\n\n";

echo "config/dynamo.php:\n";
echo json_encode([
    'default' => 'main',
    'main' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'version' => 'latest'
    ]
], JSON_PRETTY_PRINT);
echo "\n\n";

// Exemplo 8: Logs gerados
echo "9. Logs gerados:\n";
echo "O health check gera logs estruturados:\n\n";

echo "Log de sucesso:\n";
echo "- Arquivo: /tmp/im-alive.log\n";
echo "- Formato: timestamp por linha\n";
echo "- Limite: 100 entradas\n\n";

echo "Log de falha (via logger):\n";
echo "- Nível: CRITICAL\n";
echo "- Mensagem: 'Health check failed'\n";
echo "- Contexto: dead_services, response, timestamp\n\n";

// Exemplo 9: Métricas disponíveis
echo "10. Métricas disponíveis:\n";
echo "O health check retorna métricas detalhadas:\n";
echo "- total_services: Número total de serviços verificados\n";
echo "- dead_services: Número de serviços com falha\n";
echo "- healthy_services: Número de serviços saudáveis\n";
echo "- total_time: Tempo total da verificação (ms)\n";
echo "- timestamp: Timestamp da verificação\n";
echo "- status: Status geral (healthy/unhealthy)\n\n";

echo "=== Fim dos Exemplos ===\n"; 