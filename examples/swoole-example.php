<?php
declare(strict_types=1);

/**
 * Exemplo de Uso do Swoole com PHP Slim Modular Framework
 * 
 * Este arquivo demonstra como usar o Swoole de forma opcional
 * com o framework, mantendo compatibilidade total.
 * 
 * @package Core\Examples
 * @version 2.9.5
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Carrega o bootstrap da aplicação
$app = require __DIR__ . '/../src/bootstrap.php';

echo "🚀 PHP Slim Modular Framework com Swoole\n";
echo "==========================================\n\n";

// Verifica se o Swoole está disponível
if (!swoole_available()) {
    echo "❌ Swoole não está disponível\n";
    echo "📦 Para instalar: pecl install swoole\n";
    echo "🔧 Usando servidor padrão do PHP...\n\n";
    
    // Executa com servidor padrão
    $app->run();
    exit;
}

echo "✅ Swoole está disponível!\n";

// Exibe informações do Swoole
$info = swoole_info();
echo "📊 Versão: {$info['version']}\n";
echo "🔧 Funcionalidades: " . implode(', ', $info['features']) . "\n\n";

// Verifica se deve usar o Swoole
if (!should_use_swoole()) {
    echo "⚠️  Swoole não está habilitado\n";
    echo "🔧 Para habilitar: SWOOLE_ENABLED=true\n";
    echo "🚀 Usando servidor padrão do PHP...\n\n";
    
    // Executa com servidor padrão
    $app->run();
    exit;
}

echo "🚀 Iniciando servidor Swoole...\n\n";

// Configurações do servidor
$config = [
    'host' => '127.0.0.1',
    'port' => 9501,
    'workers' => 2,
    'max_request' => 1000,
    'enable_coroutine' => true,
    'log_level' => SWOOLE_LOG_INFO,
    'log_file' => __DIR__ . '/../storage/logs/swoole-example.log',
    'pid_file' => __DIR__ . '/../storage/swoole-example.pid',
    'daemonize' => false,
];

// Define configuração personalizada
\Core\Utils\Swoole::setConfig($config);

echo "📋 Configuração:\n";
echo "   Host: {$config['host']}\n";
echo "   Porta: {$config['port']}\n";
echo "   Workers: {$config['workers']}\n";
echo "   Max Request: {$config['max_request']}\n";
echo "   Coroutines: " . ($config['enable_coroutine'] ? 'Habilitado' : 'Desabilitado') . "\n\n";

// Handler de requisição
$requestHandler = function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($app) {
    try {
        // Log da requisição
        $startTime = microtime(true);
        
        // Converte requisição Swoole para PSR-7
        $psrRequest = createPsr7Request($request);
        
        // Processa através do Slim
        $psrResponse = $app->handle($psrRequest);
        
        // Converte resposta PSR-7 para Swoole
        createSwooleResponse($psrResponse, $response);
        
        // Log de performance
        $duration = (microtime(true) - $startTime) * 1000;
        echo "📝 {$request->server['request_method']} {$request->server['request_uri']} - {$duration}ms\n";
        
    } catch (\Throwable $e) {
        // Trata erros
        handleSwooleError($e, $response);
        
        // Log do erro
        echo "❌ Erro: {$e->getMessage()}\n";
    }
};

// Inicia o servidor
try {
    \Core\Utils\Swoole::startServer($requestHandler);
} catch (\Throwable $e) {
    echo "❌ Erro ao iniciar servidor: {$e->getMessage()}\n";
    exit(1);
}

/**
 * Converte requisição Swoole para PSR-7
 */
function createPsr7Request(\Swoole\Http\Request $swooleRequest): \Psr\Http\Message\ServerRequestInterface
{
    $uri = new \Slim\Psr7\Uri(
        $swooleRequest->server['request_scheme'] ?? 'http',
        $swooleRequest->header['host'] ?? 'localhost',
        $swooleRequest->server['server_port'] ?? 80,
        $swooleRequest->server['request_uri'] ?? '/',
        $swooleRequest->server['query_string'] ?? ''
    );

    $headers = [];
    foreach ($swooleRequest->header as $name => $value) {
        $headers[strtolower($name)] = $value;
    }

    $body = new \Slim\Psr7\Stream(fopen('php://temp', 'r+'));
    if ($swooleRequest->rawContent()) {
        $body->write($swooleRequest->rawContent());
        $body->rewind();
    }

    $request = new \Slim\Psr7\Request(
        $swooleRequest->server['request_method'] ?? 'GET',
        $uri,
        $headers,
        $body,
        '1.1',
        []
    );

    // Adiciona parâmetros
    if (!empty($swooleRequest->get)) {
        $request = $request->withQueryParams($swooleRequest->get);
    }
    if (!empty($swooleRequest->post)) {
        $request = $request->withParsedBody($swooleRequest->post);
    }
    if (!empty($swooleRequest->cookie)) {
        $request = $request->withCookieParams($swooleRequest->cookie);
    }

    return $request;
}

/**
 * Converte resposta PSR-7 para Swoole
 */
function createSwooleResponse(\Psr\Http\Message\ResponseInterface $psrResponse, \Swoole\Http\Response $swooleResponse): void
{
    $swooleResponse->status($psrResponse->getStatusCode());

    foreach ($psrResponse->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            $swooleResponse->header($name, $value);
        }
    }

    $body = $psrResponse->getBody();
    $body->rewind();
    $swooleResponse->end($body->getContents());
}

/**
 * Trata erros no contexto do Swoole
 */
function handleSwooleError(\Throwable $e, \Swoole\Http\Response $response): void
{
    $statusCode = 500;
    $message = 'Internal Server Error';
    
    if ($e instanceof \Slim\Exception\HttpException) {
        $statusCode = $e->getCode();
        $message = $e->getMessage();
    }
    
    $response->status($statusCode);
    $response->header('Content-Type', 'application/json');
    
    $errorData = [
        'error' => [
            'message' => $message,
            'code' => $statusCode,
            'timestamp' => date('c'),
        ]
    ];
    
    if ($_ENV['APP_DEBUG'] ?? false) {
        $errorData['error']['trace'] = $e->getTraceAsString();
        $errorData['error']['file'] = $e->getFile();
        $errorData['error']['line'] = $e->getLine();
    }
    
    $response->end(json_encode($errorData, JSON_PRETTY_PRINT));
}
