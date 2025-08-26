# Integração de Servidores

## Visão Geral

O framework agora suporta inicialização automática de servidores através do arquivo `bootstrap.php`. Isso permite um controle centralizado e flexível sobre qual servidor usar (Swoole ou padrão).

## Pontos de Entrada

### 1. `server.php` (Recomendado)
Arquivo principal na raiz do projeto que detecta automaticamente o servidor apropriado.

```bash
# Inicia automaticamente (detecta Swoole se disponível)
php server.php

# Força uso do Swoole
php server.php --swoole

# Força servidor padrão
php server.php --standard
```

### 2. `public/index.php`
Ponto de entrada tradicional que inicia automaticamente o servidor.

```bash
# Via servidor web (Apache/Nginx)
# Acesse: http://localhost

# Via servidor PHP embutido
php -S localhost:8000 -t public
```

### 3. `src/server-swoole.php`
Ponto de entrada específico para Swoole (força uso).

```bash
php src/server-swoole.php
```

## Scripts Composer

O framework fornece scripts convenientes para iniciar os servidores:

```bash
# Inicia automaticamente
composer serve

# Força Swoole
composer serve-swoole

# Força servidor padrão
composer serve-standard

# Aliases para desenvolvimento
composer dev
composer dev-swoole
composer dev-standard
```

## Configuração

### Variáveis de Ambiente

```env
# Habilita/desabilita Swoole
SWOOLE_ENABLED=true

# Configurações do Swoole
SWOOLE_HOST=0.0.0.0
SWOOLE_PORT=9501
SWOOLE_WORKERS=4
SWOOLE_MAX_REQUEST=10000
```

### Detecção Automática

O sistema detecta automaticamente:

1. **Swoole disponível**: Extensão instalada
2. **Swoole habilitado**: `SWOOLE_ENABLED=true`
3. **Fallback**: Servidor padrão do PHP

## Função `bootstrap_app()`

A função `bootstrap_app(bool $autoStart = false)` no `bootstrap.php` permite:

```php
// Retorna a aplicação sem iniciar servidor
$app = require 'src/bootstrap.php';

// Inicia servidor automaticamente
$app = require 'src/bootstrap.php';
bootstrap_app(true);
```

## Fluxo de Execução

1. **Carregamento**: `bootstrap.php` inicializa a aplicação
2. **Detecção**: Verifica disponibilidade e configuração do Swoole
3. **Decisão**: Escolhe servidor apropriado
4. **Inicialização**: Inicia o servidor selecionado

## Vantagens

- **Centralização**: Controle unificado no `bootstrap.php`
- **Flexibilidade**: Múltiplos pontos de entrada
- **Automação**: Detecção automática do servidor
- **Compatibilidade**: Mantém compatibilidade com servidor padrão
- **Facilidade**: Scripts composer para uso rápido

## Exemplos de Uso

### Desenvolvimento
```bash
# Desenvolvimento com detecção automática
composer dev

# Desenvolvimento forçando Swoole
composer dev-swoole
```

### Produção
```bash
# Produção com Swoole
SWOOLE_ENABLED=true composer serve

# Produção com servidor padrão
SWOOLE_ENABLED=false composer serve
```

### Docker
```dockerfile
# Dockerfile exemplo
FROM php:8.2-fpm

# Instala Swoole
RUN pecl install swoole

# Copia arquivos
COPY . /var/www/html

# Inicia servidor
CMD ["php", "server.php"]
```

## Troubleshooting

### Swoole não inicia
```bash
# Verifica se está instalado
php -m | grep swoole

# Verifica configuração
php -i | grep swoole
```

### Servidor padrão não funciona
```bash
# Verifica logs
tail -f storage/logs/app.log

# Verifica configuração
php -i | grep -E "(display_errors|error_reporting)"
```

## Migração

### De versões anteriores
1. Substitua chamadas diretas ao `server-swoole.php` por `server.php`
2. Use scripts composer em vez de comandos diretos
3. Configure variáveis de ambiente para controle

### Para novos projetos
1. Use `server.php` como ponto de entrada principal
2. Configure `SWOOLE_ENABLED` conforme necessário
3. Use scripts composer para desenvolvimento
