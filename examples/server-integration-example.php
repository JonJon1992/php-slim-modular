<?php

declare(strict_types=1);

/**
 * Exemplo de Integração de Servidores
 * 
 * Este exemplo demonstra como usar a nova integração centralizada
 * de servidores no framework.
 * 
 * @package Core
 * @version 2.9.6
 */

echo "Exemplo de Integração de Servidores\n";
echo "=====================================\n\n";

// Exemplo 1: Carregar aplicação sem iniciar servidor
echo "1. Carregando aplicação sem iniciar servidor:\n";
$app = require __DIR__ . '/../src/bootstrap.php';
echo "Aplicação carregada: " . get_class($app) . "\n\n";

// Exemplo 2: Verificar se Swoole está disponível
echo "2. Verificando disponibilidade do Swoole:\n";
if (function_exists('swoole_available')) {
    echo "Swoole disponível: " . (swoole_available() ? 'Sim' : 'Não') . "\n";
    echo "Swoole habilitado: " . (should_use_swoole() ? 'Sim' : 'Não') . "\n";
} else {
    echo "Helpers do Swoole não carregados\n";
}
echo "\n";

// Exemplo 3: Simular diferentes configurações
echo "3. Simulando diferentes configurações:\n";

// Configuração 1: Swoole habilitado
$_ENV['SWOOLE_ENABLED'] = 'true';
echo "Com SWOOLE_ENABLED=true:\n";
echo "- Swoole disponível: " . (class_exists('Swoole\Server') ? 'Sim' : 'Não') . "\n";
echo "- Swoole habilitado: " . ($_ENV['SWOOLE_ENABLED'] === 'true' ? 'Sim' : 'Não') . "\n";

// Configuração 2: Swoole desabilitado
$_ENV['SWOOLE_ENABLED'] = 'false';
echo "Com SWOOLE_ENABLED=false:\n";
echo "- Swoole disponível: " . (class_exists('Swoole\Server') ? 'Sim' : 'Não') . "\n";
echo "- Swoole habilitado: " . ($_ENV['SWOOLE_ENABLED'] === 'true' ? 'Sim' : 'Não') . "\n\n";

// Exemplo 4: Mostrar comandos disponíveis
echo "4. Comandos disponíveis:\n";
echo "php server.php                    # Inicia automaticamente\n";
echo "php server.php --swoole          # Força Swoole\n";
echo "php server.php --standard        # Força servidor padrão\n";
echo "composer serve                   # Inicia automaticamente\n";
echo "composer serve-swoole           # Força Swoole\n";
echo "composer serve-standard         # Força servidor padrão\n";
echo "php public/index.php            # Via servidor web\n";
echo "php src/server-swoole.php       # Swoole específico\n\n";

// Exemplo 5: Mostrar variáveis de ambiente
echo "5. Variáveis de ambiente para configuração:\n";
echo "SWOOLE_ENABLED=true/false       # Habilita/desabilita Swoole\n";
echo "SWOOLE_HOST=0.0.0.0             # Host do servidor Swoole\n";
echo "SWOOLE_PORT=9501                # Porta do servidor Swoole\n";
echo "SWOOLE_WORKERS=4                # Número de workers\n";
echo "SWOOLE_MAX_REQUEST=10000        # Máximo de requisições por worker\n\n";

// Exemplo 6: Código para usar programaticamente
echo "6. Uso programático:\n";
echo "```php\n";
echo "// Carregar aplicação\n";
echo "\$app = require 'src/bootstrap.php';\n\n";
echo "// Retornar aplicação sem iniciar servidor\n";
echo "return \$app;\n\n";
echo "// OU iniciar servidor automaticamente\n";
echo "bootstrap_app(true);\n";
echo "```\n\n";

echo "Exemplo concluído!\n";
echo "Consulte docs/SERVER_INTEGRATION.md para mais detalhes\n";
