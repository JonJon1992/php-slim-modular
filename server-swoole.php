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

// Carrega o bootstrap da aplicação
$app = require __DIR__ . '/src/bootstrap.php';

// Verifica se o Swoole está disponível
if (!\Core\Utils\Swoole::isAvailable()) {
    echo "❌ Erro: Extensão Swoole não está instalada.\n";
    echo "📦 Para instalar: pecl install swoole\n";
    echo "🔧 Alternativa: Use 'composer serve' para servidor padrão\n";
    exit(1);
}

// Verifica se deve usar o Swoole
if (!\Core\Utils\Swoole::shouldUse()) {
    echo "⚠️  Swoole não está habilitado\n";
    echo "🔧 Para habilitar: SWOOLE_ENABLED=true\n";
    echo "🚀 Usando servidor padrão do PHP...\n\n";
    
    // Executa com servidor padrão
    $app->run();
    exit;
}

echo "🚀 Iniciando servidor Swoole...\n\n";

// Handler de requisição
$requestHandler = function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($app) {
    try {
        // Converte a requisição Swoole para PSR-7
        $psrRequest = \Core\Utils\Swoole::createPsr7Request($request);
        
        // Processa a requisição através do Slim
        $psrResponse = $app->handle($psrRequest);
        
        // Converte a resposta PSR-7 para Swoole
        \Core\Utils\Swoole::createSwooleResponse($psrResponse, $response);
        
    } catch (Throwable $e) {
        // Trata erros
        \Core\Utils\Swoole::handleError($e, $response);
    }
};

// Inicia o servidor
try {
    \Core\Utils\Swoole::startServer($requestHandler);
} catch (Throwable $e) {
    echo "❌ Erro ao iniciar servidor: {$e->getMessage()}\n";
    exit(1);
}
