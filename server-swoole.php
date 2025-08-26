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

// Verifica se o Swoole está disponível
if (!extension_loaded('swoole')) {
    echo "❌ Erro: Extensão Swoole não está instalada.\n";
    echo "📦 Para instalar: pecl install swoole\n";
    echo "🔧 Alternativa: Use 'composer serve' para servidor padrão\n";
    exit(1);
}

// Carrega o bootstrap da aplicação
$app = require __DIR__ . '/src/bootstrap.php';

// Configurações do servidor Swoole
$host = $_ENV['SWOOLE_HOST'] ?? '0.0.0.0';
$port = (int) ($_ENV['SWOOLE_PORT'] ?? 9501);
$workers = (int) ($_ENV['SWOOLE_WORKERS'] ?? 4);
$maxRequest = (int) ($_ENV['SWOOLE_MAX_REQUEST'] ?? 10000);

// Cria o servidor HTTP Swoole
$http = new Swoole\HTTP\Server($host, $port);

// Configura o servidor
$http->set([
    'worker_num' => $workers,
    'max_request' => $maxRequest,
    'enable_coroutine' => true,
    'max_coroutine' => 3000,
    'log_level' => SWOOLE_LOG_INFO,
    'log_file' => __DIR__ . '/storage/logs/swoole.log',
    'pid_file' => __DIR__ . '/storage/swoole.pid',
    'daemonize' => false,
    'max_connection' => 10000,
    'heartbeat_check_interval' => 60,
    'heartbeat_idle_time' => 600,
]);

// Evento de inicialização do servidor
$http->on('start', function (Swoole\HTTP\Server $server) use ($host, $port) {
    echo "🚀 Servidor Swoole iniciado em http://{$host}:{$port}\n";
    echo "📊 Workers: {$server->setting['worker_num']}\n";
    echo "🔄 Max Request: {$server->setting['max_request']}\n";
    echo "⏹️  Para parar: Ctrl+C\n";
});

// Evento de requisição HTTP
$http->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) use ($app) {
    try {
        // Converte a requisição Swoole para PSR-7
        $psrRequest = createPsr7Request($request);
        
        // Processa a requisição através do Slim
        $psrResponse = $app->handle($psrRequest);
        
        // Converte a resposta PSR-7 para Swoole
        createSwooleResponse($psrResponse, $response);
        
    } catch (Throwable $e) {
        // Trata erros
        handleSwooleError($e, $response);
    }
});

// Evento de worker start
$http->on('workerStart', function (Swoole\HTTP\Server $server, int $workerId) {
    echo "👷 Worker {$workerId} iniciado (PID: " . posix_getpid() . ")\n";
});

// Evento de worker stop
$http->on('workerStop', function (Swoole\HTTP\Server $server, int $workerId) {
    echo "🛑 Worker {$workerId} parado\n";
});

// Inicia o servidor
$http->start();

/**
 * Converte requisição Swoole para PSR-7
 */
function createPsr7Request(Swoole\Http\Request $swooleRequest): \Psr\Http\Message\ServerRequestInterface
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

    // Adiciona parâmetros da query string
    if (!empty($swooleRequest->get)) {
        $request = $request->withQueryParams($swooleRequest->get);
    }

    // Adiciona parâmetros do POST
    if (!empty($swooleRequest->post)) {
        $request = $request->withParsedBody($swooleRequest->post);
    }

    // Adiciona cookies
    if (!empty($swooleRequest->cookie)) {
        $request = $request->withCookieParams($swooleRequest->cookie);
    }

    // Adiciona arquivos
    if (!empty($swooleRequest->files)) {
        $request = $request->withUploadedFiles(createUploadedFiles($swooleRequest->files));
    }

    return $request;
}

/**
 * Converte resposta PSR-7 para Swoole
 */
function createSwooleResponse(\Psr\Http\Message\ResponseInterface $psrResponse, Swoole\Http\Response $swooleResponse): void
{
    // Define o status code
    $swooleResponse->status($psrResponse->getStatusCode());

    // Define os headers
    foreach ($psrResponse->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            $swooleResponse->header($name, $value);
        }
    }

    // Define o corpo da resposta
    $body = $psrResponse->getBody();
    $body->rewind();
    $content = $body->getContents();
    
    $swooleResponse->end($content);
}

/**
 * Cria arquivos enviados no formato PSR-7
 */
function createUploadedFiles(array $files): array
{
    $uploadedFiles = [];
    
    foreach ($files as $fieldName => $file) {
        if (is_array($file['name'])) {
            // Múltiplos arquivos
            $uploadedFiles[$fieldName] = [];
            foreach ($file['name'] as $index => $name) {
                $uploadedFiles[$fieldName][] = new \Slim\Psr7\UploadedFile(
                    $file['tmp_name'][$index],
                    $name,
                    $file['type'][$index],
                    $file['size'][$index],
                    $file['error'][$index]
                );
            }
        } else {
            // Arquivo único
            $uploadedFiles[$fieldName] = new \Slim\Psr7\UploadedFile(
                $file['tmp_name'],
                $file['name'],
                $file['type'],
                $file['size'],
                $file['error']
            );
        }
    }
    
    return $uploadedFiles;
}

/**
 * Trata erros no contexto do Swoole
 */
function handleSwooleError(Throwable $e, Swoole\Http\Response $response): void
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
