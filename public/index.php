<?php

declare(strict_types=1);

/**
 * Ponto de entrada da aplicação
 * 
 * Este arquivo inicia automaticamente o servidor apropriado
 * baseado na configuração do ambiente.
 * 
 * @package Core
 * @version 2.9.6
 */

// Carrega o bootstrap e inicia o servidor automaticamente
$app = require __DIR__ . '/../src/bootstrap.php';

// Inicia o servidor (Swoole se disponível e habilitado, senão padrão)
bootstrap_app(true); 