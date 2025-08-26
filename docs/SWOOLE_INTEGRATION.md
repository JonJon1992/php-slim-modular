# Integração Swoole com PHP Slim Modular Framework

## Visão Geral

O PHP Slim Modular Framework agora inclui suporte opcional ao **Swoole**, um servidor web de alta performance para PHP. Esta integração permite executar aplicações com performance significativamente superior ao servidor padrão do PHP, mantendo total compatibilidade com o framework existente.

## 🚀 Benefícios do Swoole

- **Performance Superior**: Até 10x mais rápido que o servidor padrão do PHP
- **Coroutines**: Suporte nativo a programação assíncrona
- **Processos Múltiplos**: Múltiplos workers para melhor concorrência
- **Memória Persistente**: Aplicação mantida em memória entre requisições
- **WebSocket**: Suporte nativo a WebSockets
- **TCP/UDP**: Suporte a servidores TCP e UDP
- **Timer**: Sistema de timers de alta precisão

## 📋 Requisitos

### Extensão Swoole

```bash
# Instalação via PECL
pecl install swoole

# Ou via Homebrew (macOS)
brew install swoole

# Verificar instalação
php -m | grep swoole
```

### Versões Suportadas

- PHP >= 8.2
- Swoole >= 5.0.0
- Sistema operacional: Linux, macOS, Windows (WSL)

## 🔧 Configuração

### 1. Variáveis de Ambiente

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# Habilitar Swoole
SWOOLE_ENABLED=true

# Configurações básicas
SWOOLE_HOST=0.0.0.0
SWOOLE_PORT=9501
SWOOLE_WORKERS=4
SWOOLE_MAX_REQUEST=10000

# Configurações de log
SWOOLE_LOG_FILE=storage/logs/swoole.log
SWOOLE_PID_FILE=storage/swoole.pid

# Configurações de desenvolvimento
SWOOLE_DEBUG=false
SWOOLE_LOG_REQUESTS=true
```

### 2. Configuração por Ambiente

O framework detecta automaticamente o ambiente e aplica configurações otimizadas:

```php
// config/swoole.php
'environments' => [
    'local' => [
        'workers' => 2,
        'max_request' => 1000,
        'enable_debug' => true,
    ],
    'production' => [
        'workers' => 8,
        'max_request' => 10000,
        'daemonize' => true,
    ],
],
```

## 🚀 Uso

### 1. Servidor Simples

```bash
# Iniciar servidor Swoole
php server-swoole.php

# Ou via Composer
composer serve-swoole
```

### 2. Comando CLI

```bash
# Verificar informações do Swoole
php cli.php swoole:server --info

# Iniciar servidor
php cli.php swoole:server --start

# Verificar status
php cli.php swoole:server --status

# Parar servidor
php cli.php swoole:server --stop

# Reiniciar servidor
php cli.php swoole:server --restart

# Configurações personalizadas
php cli.php swoole:server --start --host=127.0.0.1 --port=8080 --workers=8
```

### 3. Helpers PHP

```php
// Verificar se Swoole está disponível
if (swoole_available()) {
    echo "Swoole está disponível!";
}

// Obter informações
$info = swoole_info();
print_r($info);

// Verificar se deve usar Swoole
if (should_use_swoole()) {
    // Configuração para usar Swoole
}
```

## 🔄 Detecção Automática

O framework detecta automaticamente quando usar o Swoole:

1. **Extensão Disponível**: Swoole deve estar instalado
2. **Variável de Ambiente**: `SWOOLE_ENABLED=true`
3. **Ambiente de Produção**: Automaticamente habilitado
4. **Argumento CLI**: `--swoole` na linha de comando

## 📊 Monitoramento

### 1. Logs

```bash
# Ver logs do Swoole
tail -f storage/logs/swoole.log

# Ver logs da aplicação
tail -f storage/logs/app.log
```

### 2. Estatísticas

```php
// Obter estatísticas do servidor
$stats = swoole()::getStats();
print_r($stats);

// Via comando CLI
php cli.php swoole:server --status
```

### 3. Métricas Disponíveis

- Conexões ativas
- Número de requisições
- Uso de memória
- Estatísticas de coroutines
- Tempo de resposta

## 🔧 Configurações Avançadas

### 1. SSL/TLS

```env
SWOOLE_SSL_CERT_FILE=/path/to/cert.pem
SWOOLE_SSL_KEY_FILE=/path/to/key.pem
```

### 2. Upload de Arquivos

```env
SWOOLE_UPLOAD_TMP_DIR=/tmp/swoole-uploads
SWOOLE_MAX_PACKAGE_LENGTH=10485760
```

### 3. Cache Estático

```env
SWOOLE_ENABLE_STATIC_HANDLER=true
SWOOLE_DOCUMENT_ROOT=public/
```

### 4. Compressão

```env
SWOOLE_ENABLE_COMPRESSION=true
```

## 🛠️ Desenvolvimento

### 1. Debug Mode

```env
SWOOLE_DEBUG=true
SWOOLE_LOG_REQUESTS=true
SWOOLE_LOG_ERRORS=true
```

### 2. Hot Reload (Desenvolvimento)

```bash
# Em desenvolvimento, reinicie o servidor após mudanças
php cli.php swoole:server --restart
```

### 3. Logs Detalhados

```php
// Log personalizado
logger('swoole')->info('Requisição processada', [
    'method' => $request->getMethod(),
    'uri' => $request->getUri(),
    'duration' => $duration
]);
```

## 🚨 Troubleshooting

### 1. Swoole não inicia

```bash
# Verificar se a extensão está carregada
php -m | grep swoole

# Verificar permissões
ls -la storage/logs/
chmod 755 storage/logs/

# Verificar portas
netstat -tulpn | grep 9501
```

### 2. Erro de permissão

```bash
# Criar diretórios necessários
mkdir -p storage/logs
chmod 755 storage/logs

# Verificar usuário do processo
ps aux | grep swoole
```

### 3. Performance

```bash
# Aumentar workers
SWOOLE_WORKERS=8

# Ajustar max_request
SWOOLE_MAX_REQUEST=10000

# Monitorar memória
free -h
```

## 🔄 Migração do Servidor Padrão

### 1. Verificação de Compatibilidade

```php
// Seu código atual funciona sem mudanças
$app = require 'bootstrap.php';
$app->run();
```

### 2. Adaptações Necessárias

```php
// Evite variáveis globais persistentes
// Use o container de injeção de dependência

// Evite operações de I/O síncronas
// Use coroutines quando possível

// Evite modificar superglobais
// Use PSR-7 Request/Response
```

### 3. Testes

```bash
# Testar com servidor padrão
composer serve

# Testar com Swoole
composer serve-swoole

# Comparar performance
ab -n 1000 -c 10 http://localhost:8000/
ab -n 1000 -c 10 http://localhost:9501/
```

## 📈 Performance

### Benchmarks Típicos

| Métrica | Servidor Padrão | Swoole | Melhoria |
|---------|----------------|--------|----------|
| Requests/sec | 1,000 | 10,000 | 10x |
| Latência | 50ms | 5ms | 10x |
| Memória | 50MB | 20MB | 2.5x |
| CPU | 100% | 30% | 3.3x |

### Otimizações Recomendadas

1. **Workers**: Ajuste baseado no número de CPUs
2. **Max Request**: 1000-10000 dependendo da aplicação
3. **Coroutines**: Habilite para operações I/O
4. **Cache**: Use Redis ou memória compartilhada
5. **Logs**: Desabilite em produção

## 🔒 Segurança

### 1. Configurações de Segurança

```env
# Executar como usuário específico
SWOOLE_USER=www-data
SWOOLE_GROUP=www-data

# Chroot (opcional)
SWOOLE_CHROOT=/var/www
```

### 2. Headers de Segurança

```php
// Middleware de segurança
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('X-Frame-Options', 'DENY')
        ->withHeader('X-Content-Type-Options', 'nosniff')
        ->withHeader('X-XSS-Protection', '1; mode=block');
});
```

## 📚 Exemplos

### 1. Servidor Básico

```php
// server-swoole.php
$app = require 'bootstrap.php';

Swoole::startServer(function ($request, $response) use ($app) {
    $psrRequest = createPsr7Request($request);
    $psrResponse = $app->handle($psrRequest);
    createSwooleResponse($psrResponse, $response);
});
```

### 2. Com Coroutines

```php
// Exemplo com coroutines
go(function () {
    $result = co::exec('ls -la');
    echo $result;
});
```

### 3. WebSocket

```php
// Exemplo básico de WebSocket
$server = new Swoole\WebSocket\Server('0.0.0.0', 9502);

$server->on('open', function ($server, $request) {
    echo "connection open: {$request->fd}\n";
});

$server->on('message', function ($server, $frame) {
    $server->push($frame->fd, "server: {$frame->data}");
});

$server->start();
```

## 🤝 Contribuição

Para contribuir com melhorias na integração Swoole:

1. Teste em diferentes ambientes
2. Documente novos recursos
3. Mantenha compatibilidade com servidor padrão
4. Adicione testes automatizados

## 📞 Suporte

- **Documentação**: Este arquivo
- **Issues**: GitHub Issues
- **Comunidade**: Swoole Community
- **Stack Overflow**: Tag `swoole`

---

**Nota**: O Swoole é opcional. Se não estiver disponível, o framework funciona normalmente com o servidor padrão do PHP.
