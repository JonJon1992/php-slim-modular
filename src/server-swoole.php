<?php
declare(strict_types=1);

/**
 * Servidor Swoole para PHP Slim Modular Framework
 * 
 * Este arquivo permite executar a aplicação usando o Swoole como servidor web
 * de alta performance. O uso é opcional e requer a extensão Swoole instalada.
 * 
 * @package Core
 * @version 2.9.6
 */

// Força o uso do Swoole
$_ENV['SWOOLE_ENABLED'] = 'true';

// Carrega o bootstrap e inicia o servidor Swoole
$app = require __DIR__ . '/bootstrap.php';

// Inicia o servidor Swoole
bootstrap_app(true);
