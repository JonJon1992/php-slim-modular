<?php

declare(strict_types=1);

/**
 * Servidor da Aplicação
 * 
 * Ponto de entrada alternativo para iniciar a aplicação.
 * Detecta automaticamente se deve usar Swoole ou servidor padrão.
 * 
 * Uso:
 * - php server.php (inicia automaticamente)
 * - php server.php --swoole (força uso do Swoole)
 * - php server.php --standard (força servidor padrão)
 * 
 * @package Core
 * @version 2.9.6
 */

// Verifica argumentos da linha de comando
$forceSwoole = in_array('--swoole', $argv ?? []);
$forceStandard = in_array('--standard', $argv ?? []);

// Define variáveis de ambiente baseado nos argumentos
if ($forceSwoole) {
    $_ENV['SWOOLE_ENABLED'] = 'true';
    echo "Forçando uso do Swoole...\n";
} elseif ($forceStandard) {
    $_ENV['SWOOLE_ENABLED'] = 'false';
    echo "Forçando servidor padrão...\n";
}

// Carrega o bootstrap
$app = require __DIR__ . '/src/bootstrap.php';

// Inicia o servidor automaticamente
bootstrap_app(true);
