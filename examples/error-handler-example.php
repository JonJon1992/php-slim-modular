<?php

declare(strict_types=1);

/**
 * Exemplo de Uso do ErrorHandler Melhorado
 * 
 * Este arquivo demonstra como o ErrorHandler refatorado funciona
 * com o Slim Framework e como ele se integra com o ExceptionHandler.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Exceptions\ErrorHandler;
use Core\Exceptions\ExceptionHandler;
use Core\Exceptions\ValidatorException;
use Core\Exceptions\AuthenticationException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponse;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

// Simular configuração básica
if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed {
        return $default;
    }
}

if (!function_exists('auth')) {
    function auth(): mixed {
        return (object) ['id' => 123];
    }
}

// Criar uma aplicação Slim de exemplo
$app = AppFactory::create();

// Configurar o ErrorHandler melhorado
$errorHandler = new ErrorHandler(
    $app->getCallableResolver(),
    $app->getResponseFactory(),
    new ExceptionHandler()
);

// Adicionar middleware de erro
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Rota de exemplo que gera diferentes tipos de erro
$app->get('/test/{type}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
    $type = $args['type'] ?? 'unknown';
    
    switch ($type) {
        case 'validation':
            throw new ValidatorException('Dados inválidos fornecidos', 422);
            
        case 'auth':
            throw new AuthenticationException('Usuário não autenticado', 401);
            
        case 'not-found':
            throw new \Slim\Exception\HttpNotFoundException($request, 'Recurso não encontrado');
            
        case 'method-not-allowed':
            throw new \Slim\Exception\HttpMethodNotAllowedException($request, ['GET', 'POST'], 'Método não permitido');
            
        case 'server-error':
            throw new \RuntimeException('Erro interno do servidor', 500);
            
        case 'domain':
            throw new \DomainException('Regra de negócio violada', 400);
            
        default:
            return $response->withJson([
                'message' => 'Tipo de erro válido',
                'available_types' => [
                    'validation' => 'Erro de validação (422)',
                    'auth' => 'Erro de autenticação (401)',
                    'not-found' => 'Recurso não encontrado (404)',
                    'method-not-allowed' => 'Método não permitido (405)',
                    'server-error' => 'Erro interno do servidor (500)',
                    'domain' => 'Erro de domínio (400)',
                ]
            ]);
    }
});

// Rota para testar CORS
$app->options('/test/{type}', function (ServerRequestInterface $request, ResponseInterface $response) {
    return $response->withStatus(200);
});

// Rota de exemplo que funciona normalmente
$app->get('/success', function (ServerRequestInterface $request, ResponseInterface $response) {
    return $response->withJson([
        'message' => 'Operação bem-sucedida',
        'timestamp' => date('c'),
        'user_id' => auth()->id ?? null
    ]);
});

echo "=== DEMONSTRAÇÃO DO ERRORHANDLER MELHORADO ===\n\n";

echo "Este exemplo demonstra como o ErrorHandler refatorado funciona:\n\n";

echo "1. Integração com ExceptionHandler:\n";
echo "   - O ErrorHandler usa o ExceptionHandler para processar exceções\n";
echo "   - Mantém a consistência no formato de resposta\n";
echo "   - Reutiliza a lógica de logging e mapeamento de status codes\n\n";

echo "2. Melhorias implementadas:\n";
echo "   ✅ Tipagem forte com declare(strict_types=1)\n";
echo "   ✅ Injeção de dependência do ExceptionHandler\n";
echo "   ✅ Uso de métodos PSR-7 em vez de filter_input()\n";
echo "   ✅ Detecção inteligente de IP do cliente\n";
echo "   ✅ Informações de debug mais detalhadas\n";
echo "   ✅ Headers HTTP apropriados\n";
echo "   ✅ Suporte melhorado a CORS\n\n";

echo "3. Como testar:\n";
echo "   - Execute: php -S localhost:8000 -t examples/\n";
echo "   - Acesse: http://localhost:8000/error-handler-example.php\n";
echo "   - Teste diferentes tipos de erro:\n";
echo "     * http://localhost:8000/test/validation\n";
echo "     * http://localhost:8000/test/auth\n";
echo "     * http://localhost:8000/test/not-found\n";
echo "     * http://localhost:8000/test/method-not-allowed\n";
echo "     * http://localhost:8000/test/server-error\n";
echo "     * http://localhost:8000/test/domain\n\n";

echo "4. Exemplo de resposta de erro:\n";
echo "{\n";
echo "  \"trace-id\": \"550e8400-e29b-41d4-a716-446655440000\",\n";
echo "  \"status\": 422,\n";
echo "  \"message\": \"Dados inválidos fornecidos\",\n";
echo "  \"timestamp\": \"2024-01-15T10:30:00+00:00\",\n";
echo "  \"path\": \"/test/validation\",\n";
echo "  \"request\": {\n";
echo "    \"method\": \"GET\",\n";
echo "    \"uri\": \"/test/validation\",\n";
echo "    \"user_agent\": \"Mozilla/5.0...\",\n";
echo "    \"host\": \"localhost:8000\",\n";
echo "    \"ip\": \"127.0.0.1\"\n";
echo "  },\n";
echo "  \"user\": {\n";
echo "    \"id\": 123\n";
echo "  },\n";
echo "  \"debug\": {\n";
echo "    \"exception\": {\n";
echo "      \"class\": \"Core\\\\Exceptions\\\\ValidatorException\",\n";
echo "      \"file\": \"/app/src/Validators/UserValidator.php\",\n";
echo "      \"line\": 45,\n";
echo "      \"code\": 422,\n";
echo "      \"trace\": \"Stack trace...\"\n";
echo "    },\n";
echo "    \"request\": {\n";
echo "      \"method\": \"GET\",\n";
echo "      \"uri\": \"/test/validation\",\n";
echo "      \"headers\": {...},\n";
echo "      \"query_params\": {...}\n";
echo "    }\n";
echo "  }\n";
echo "}\n\n";

echo "5. Benefícios da refatoração:\n";
echo "   - Código mais limpo e organizado\n";
echo "   - Reutilização de lógica entre handlers\n";
echo "   - Melhor testabilidade com injeção de dependência\n";
echo "   - Consistência no formato de resposta\n";
echo "   - Informações de debug mais úteis\n";
echo "   - Headers HTTP apropriados para APIs\n\n";

echo "=== FIM DA DEMONSTRAÇÃO ===\n";

// Se executado diretamente, mostrar informações
if (php_sapi_name() === 'cli') {
    echo "\nPara testar com o servidor web:\n";
    echo "1. cd examples/\n";
    echo "2. php -S localhost:8000\n";
    echo "3. Acesse http://localhost:8000/error-handler-example.php\n";
} 