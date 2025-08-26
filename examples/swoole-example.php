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

echo "PHP Slim Modular Framework com Swoole\n";
echo "==========================================\n\n";

// Verifica se o Swoole está disponível
if (!swoole_available()) {
    echo "Swoole não está disponível\n";
    echo "Para instalar: pecl install swoole\n";
    echo "Usando servidor padrão do PHP...\n\n";
    
    // Executa com servidor padrão
    $app->run();
    exit;
}

    echo "Swoole está disponível!\n";

// Exibe informações do Swoole
$info = swoole_info();
echo "Versão: {$info['version']}\n";
echo "Funcionalidades: " . implode(', ', $info['features']) . "\n\n";

// Verifica se deve usar o Swoole
if (!should_use_swoole()) {
    echo "Swoole não está habilitado\n";
    echo "Para habilitar: SWOOLE_ENABLED=true\n";
    echo "Usando servidor padrão do PHP...\n\n";
    
    // Executa com servidor padrão
    $app->run();
    exit;
}

    echo "Iniciando servidor Swoole...\n\n";

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
        $psrRequest = \Core\Utils\Swoole::createPsr7Request($request);
        
        // Processa através do Slim
        $psrResponse = $app->handle($psrRequest);
        
        // Converte resposta PSR-7 para Swoole
        \Core\Utils\Swoole::createSwooleResponse($psrResponse, $response);
        
        // Log de performance
        $duration = (microtime(true) - $startTime) * 1000;
        echo "📝 {$request->server['request_method']} {$request->server['request_uri']} - {$duration}ms\n";
        
    } catch (Throwable $e) {
        // Trata erros
        \Core\Utils\Swoole::handleError($e, $response);
        
        // Log do erro
        echo "Erro: {$e->getMessage()}\n";
    }
};

// Inicia o servidor
try {
    \Core\Utils\Swoole::startServer($requestHandler);
} catch (Throwable $e) {
    echo "Erro ao iniciar servidor: {$e->getMessage()}\n";
    exit(1);
}
