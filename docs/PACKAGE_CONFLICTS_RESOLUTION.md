# Resolução de Conflitos de Pacotes - MS Bot Automation

## Visão Geral

Este documento relata os conflitos de dependências encontrados entre os pacotes utilizados no projeto MS Bot Automation e as soluções implementadas para resolvê-los.

## Pacotes Envolvidos

- `jonjon1992/php-slim-modular` (v2.2.4)
- `jonjon1992/s3-php` (v2.1.0)
- `illuminate/support` (v10.48.28)

## Problemas Identificados

### 1. Conflito de Função `s3()`

**Problema:**
```
PHP Fatal error: Cannot redeclare s3() (previously declared in /var/www/vendor/jonjon1992/s3-php/lib/helper.php:14) in /var/www/vendor/jonjon1992/php-slim-modular/src/helpers.php on line 151
```

**Causa:**
Ambos os pacotes `s3-php` e `php-slim-modular` declaravam a função `s3()`:
- `s3-php/lib/helper.php:14` - Declaração com verificação `if (!function_exists('s3'))`
- `php-slim-modular/src/helpers.php:151` - Declaração sem verificação

**Solução Aplicada:**
Modificado o arquivo `vendor/jonjon1992/php-slim-modular/src/helpers.php`:

```php
// ANTES
function s3(string $alias = null)
{
    $alias = $alias ?? app()->module() ?? config('storage.default', array_key_first(config('storage')));
    return \S3\Lib\S3::singleton()->bucket($alias);
}

// DEPOIS
if (!function_exists('s3')) {
    function s3(string $alias = null)
    {
        $alias = $alias ?? app()->module() ?? config('storage.default', array_key_first(config('storage')));
        return \S3\Lib\S3::singleton()->bucket($alias);
    }
}
```

### 2. Incompatibilidade de Assinaturas na Classe `Str`

**Problema:**
```
PHP Fatal error: Declaration of Core\Utils\Str::camel(string $value): string must be compatible with Illuminate\Support\Str::camel($value)
```

**Causa:**
A classe `Str` do `php-slim-modular` estende `Illuminate\Support\Str` mas redeclara métodos com assinaturas incompatíveis.

**Métodos Afetados:**
- `camel(string $value): string` → `camel($value): string`
- `endsWith(string $value, string $cap): bool` → `endsWith($value, $cap): bool`
- `studly(string $value): string` → `studly($value): string`
- `ucfirst(string $value): string` → `ucfirst($value): string`

**Solução Aplicada:**
Modificado o arquivo `vendor/jonjon1992/php-slim-modular/src/Utils/Str.php`:

```php
// ANTES
public static function camel(string $value): string
{
    return lcfirst(static::studly($value));
}

// DEPOIS
public static function camel($value): string
{
    return lcfirst(static::studly($value));
}
```

### 3. Configuração de Storage Ausente

**Problema:**
```
PHP Fatal error: Uncaught InvalidArgumentException: Alias cannot be empty in /var/www/vendor/jonjon1992/s3-php/lib/S3.php:37
```

**Causa:**
O sistema tentava configurar o S3 mas não havia arquivo de configuração de storage.

**Solução Aplicada:**
Criado arquivo `configs/storage.php`:

```php
<?php

return [
    'default' => 's3',
    's3' => [
        'driver' => 's3',
        'access_key' => 'test',
        'secret_key' => 'test',
        'region' => 'us-east-1',
        'bucket' => 'test-bucket',
        'endpoint' => 'http://localhost:4566',
        'use_path_style_endpoint' => true,
    ],
];
```

## Arquivos Modificados

### 1. `vendor/jonjon1992/php-slim-modular/src/helpers.php`
- **Linha ~151**: Adicionado `if (!function_exists('s3'))` para evitar redeclaração da função

### 2. `vendor/jonjon1992/php-slim-modular/src/Utils/Str.php`
- **Linha ~62**: Removido type hint `string` do parâmetro `$value` em `camel()`
- **Linha ~53**: Removido type hint `string` dos parâmetros `$value` e `$cap` em `endsWith()`
- **Linha ~80**: Removido type hint `string` do parâmetro `$value` em `studly()`
- **Linha ~71**: Removido type hint `string` do parâmetro `$value` em `ucfirst()`

### 3. `configs/storage.php` (Novo arquivo)
- Criado arquivo de configuração para resolver erro de configuração do S3

## Impacto das Mudanças

### Positivo:
- ✅ Aplicação funcionando sem erros 500
- ✅ Conflitos de dependências resolvidos
- ✅ Compatibilidade mantida com Illuminate Support
- ✅ Configuração de S3 funcional

### Considerações:
- ⚠️ Modificações em arquivos do vendor (podem ser sobrescritas em atualizações)
- ⚠️ Necessário reaplicar correções após `composer update`
- ⚠️ Configuração de S3 simplificada para ambiente de desenvolvimento

## Recomendações Futuras

### 1. Fork dos Pacotes
Considerar fazer fork dos pacotes problemáticos para aplicar as correções permanentemente:
- `jonjon1992/php-slim-modular`
- `jonjon1992/s3-php`

### 2. Script de Correção Automática
Criar script para aplicar correções automaticamente após `composer install`:

```bash
#!/bin/bash
# scripts/fix-package-conflicts.sh

# Corrigir função s3()
sed -i 's/function s3(/if (!function_exists('\''s3'\'')) {\n    function s3(/g' vendor/jonjon1992/php-slim-modular/src/helpers.php
sed -i 's/return \\S3\\Lib\\S3::singleton()->bucket($alias);/return \\S3\\Lib\\S3::singleton()->bucket($alias);\n    }/g' vendor/jonjon1992/php-slim-modular/src/helpers.php

# Corrigir assinaturas da classe Str
sed -i 's/public static function camel(string $value): string/public static function camel($value): string/g' vendor/jonjon1992/php-slim-modular/src/Utils/Str.php
sed -i 's/public static function endsWith(string $value, string $cap): bool/public static function endsWith($value, $cap): bool/g' vendor/jonjon1992/php-slim-modular/src/Utils/Str.php
sed -i 's/public static function studly(string $value): string/public static function studly($value): string/g' vendor/jonjon1992/php-slim-modular/src/Utils/Str.php
sed -i 's/public static function ucfirst(string $value): string/public static function ucfirst($value): string/g' vendor/jonjon1992/php-slim-modular/src/Utils/Str.php
```

### 3. Issue nos Repositórios
Abrir issues nos repositórios dos pacotes para reportar os problemas:
- [jonjon1992/php-slim-modular](https://github.com/JonJon1992/php-slim-modular)
- [jonjon1992/s3-php](https://github.com/JonJon1992/s3-php)

## Testes Realizados

### 1. Teste de Autoload
```bash
docker exec ms-bot-automation-api php -r "require '/var/www/vendor/autoload.php'; echo 'Autoload OK';"
```
✅ **Resultado**: Autoload funcionando

### 2. Teste de Bootstrap
```bash
docker exec ms-bot-automation-api php -r "require '/var/www/vendor/autoload.php'; require '/var/www/vendor/jonjon1992/php-slim-modular/src/bootstrap.php'; echo 'Bootstrap OK';"
```
✅ **Resultado**: Bootstrap funcionando

### 3. Teste da Aplicação
```bash
docker exec ms-bot-automation-api php /var/www/public/index.php
```
✅ **Resultado**: Aplicação funcionando

### 4. Teste da API
```bash
curl -s http://localhost:8088/im-alive?force
curl -s http://localhost:8088/v1/produtos
```
✅ **Resultado**: API respondendo corretamente

## Conclusão

Os conflitos de pacotes foram identificados e resolvidos com sucesso. A aplicação está funcionando corretamente após as correções aplicadas. Recomenda-se documentar essas mudanças e criar processos para reaplicar as correções quando necessário.

---

**Data da Resolução**: 05/08/2025  
**Responsável**: Equipe de Desenvolvimento  
**Status**: ✅ Resolvido 