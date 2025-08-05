<?php

declare(strict_types=1);

/**
 * Exemplo de teste do Bootstrap
 * 
 * Este exemplo demonstra como testar se o bootstrap está funcionando
 * corretamente com a correção do método useAttributes().
 * 
 * @package Examples
 * @author JonJon1992
 * @version 2.4.0
 */

require_once __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use Core\Utils\Path;

echo "=== Bootstrap Test Example ===\n\n";

// Teste 1: Verificar se o ContainerBuilder está disponível
echo "1. Verificando ContainerBuilder:\n";
if (class_exists(ContainerBuilder::class)) {
    echo "✓ ContainerBuilder está disponível\n";
    
    $containerBuilder = new ContainerBuilder();
    echo "✓ ContainerBuilder instanciado com sucesso\n";
    
    // Teste 2: Verificar métodos disponíveis
    echo "\n2. Verificando métodos disponíveis:\n";
    
    $methods = [
        'useAutowiring' => 'Configuração de autowiring',
        'useAttributes' => 'Configuração de atributos (PHP-DI 7.0+)',
        'addDefinitions' => 'Adição de definições',
        'build' => 'Construção do container'
    ];
    
    foreach ($methods as $method => $description) {
        if (method_exists($containerBuilder, $method)) {
            echo "✓ {$method}() - {$description}\n";
        } else {
            echo "✗ {$method}() - {$description} (NÃO DISPONÍVEL)\n";
        }
    }
    
    // Teste 3: Testar configuração básica
    echo "\n3. Testando configuração básica:\n";
    try {
        $containerBuilder->useAutowiring(true);
        echo "✓ useAutowiring(true) executado com sucesso\n";
        
        // Testar useAttributes com verificação
        if (method_exists($containerBuilder, 'useAttributes')) {
            $containerBuilder->useAttributes(true);
            echo "✓ useAttributes(true) executado com sucesso\n";
        } else {
            echo "⚠ useAttributes() não está disponível (PHP-DI < 7.0)\n";
        }
        
        // Adicionar uma definição simples
        $containerBuilder->addDefinitions([
            'test' => 'test-value'
        ]);
        echo "✓ addDefinitions() executado com sucesso\n";
        
        // Construir o container
        $container = $containerBuilder->build();
        echo "✓ Container construído com sucesso\n";
        
        // Testar se a definição foi adicionada
        if ($container->has('test')) {
            $value = $container->get('test');
            echo "✓ Container retornou valor: {$value}\n";
        } else {
            echo "✗ Container não possui a definição 'test'\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Erro na configuração: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "✗ ContainerBuilder não está disponível\n";
}

// Teste 4: Verificar arquivos necessários
echo "\n4. Verificando arquivos necessários:\n";
$requiredFiles = [
    'bootstrap.php' => Path::src('bootstrap.php'),
    'bindings.php' => Path::src('bindings.php'),
    'services.php' => Path::src('services.php'),
    'middlewares.php' => Path::src('middlewares.php'),
    'routes.php' => Path::src('routes.php'),
    'events.php' => Path::src('events.php')
];

foreach ($requiredFiles as $name => $path) {
    if (file_exists($path)) {
        echo "✓ {$name} existe: {$path}\n";
    } else {
        echo "✗ {$name} não encontrado: {$path}\n";
    }
}

// Teste 5: Verificar sintaxe dos arquivos
echo "\n5. Verificando sintaxe dos arquivos:\n";
foreach ($requiredFiles as $name => $path) {
    if (file_exists($path)) {
        $output = [];
        $returnCode = 0;
        exec("php -l {$path} 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            echo "✓ {$name} - Sintaxe OK\n";
        } else {
            echo "✗ {$name} - Erro de sintaxe:\n";
            foreach ($output as $line) {
                echo "  {$line}\n";
            }
        }
    }
}

// Teste 6: Informações sobre versões
echo "\n6. Informações sobre versões:\n";
echo "PHP Version: " . PHP_VERSION . "\n";

// Verificar versão do PHP-DI
$composerLock = json_decode(file_get_contents(Path::root('/composer.lock')), true);
if ($composerLock) {
    foreach ($composerLock['packages'] as $package) {
        if ($package['name'] === 'php-di/php-di') {
            echo "PHP-DI Version: " . $package['version'] . "\n";
            break;
        }
    }
}

// Teste 7: Recomendações
echo "\n7. Recomendações:\n";
echo "- Se useAttributes() não estiver disponível, considere atualizar para PHP-DI 7.0+\n";
echo "- O código atual é compatível com PHP-DI 6.x e 7.x\n";
echo "- Para usar atributos, atualize: composer require php-di/php-di:^7.0\n";

echo "\n=== Fim do Teste ===\n"; 