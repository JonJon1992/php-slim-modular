# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.4] - 2024-12-19

### Fixed
- Corrigido uso de `HttpException` para `HttpInternalServerErrorException` no `Action.php`
- Criadas classes de exceção personalizadas para melhor tratamento de erros:
  - `AuthenticationException` para erros de autenticação
  - `InvalidRequestException` para erros de validação de requisição
  - `CommandException` para erros de execução de comandos
  - `AppException` para erros gerais da aplicação
- Atualizado uso de `Exception` genérica para exceções específicas em todo o framework
- Melhorada segurança de tipos e tratamento de erros

## [2.2.3] - 2024-12-19

### Fixed
- Resolvido conflito adicional de dependências com `slim/psr7` e `slim/slim`
- Atualizado `slim/psr7` de `1.6.1` para `^1.7` para suportar `psr/http-message ^2.0`
- Atualizado `slim/slim` de `4.8.1` para `^4.9` para melhor compatibilidade
- Corrigidos todos os conflitos de dependências PSR HTTP Message

## [2.2.2] - 2024-12-19

### Fixed
- Resolvido conflito de dependências entre `aws/aws-sdk-php` e `slim/php-view`
- Atualizado `slim/php-view` de `3.1.0` para `^3.2` para suportar `psr/http-message ^2.0`
- Corrigido problema de compatibilidade com AWS SDK PHP ^3.350

## [2.2.1] - 2024-12-19

### Changed
- Atualizado `jonjon1992/s3-php` de `v2.0.0` para `v2.1.0`

## [2.2.0] - 2024-12-19

### Added
- AWS SDK PHP atualizado para versão ^3.350
- Função helper `s3()` para acessar buckets S3
- Função helper `queue()` para acessar clientes SQS
- Métodos `endsWith()`, `camel()`, `ucfirst()` e `studly()` na classe `Str`

### Fixed
- Corrigido uso de `Exception` sem namespace em múltiplos arquivos
- Removida propriedade `$client` não utilizada no `SqsClient`
- Corrigida variável `$service` não definida no `ImAliveAction`
- Adicionado import da classe `Str` no `Trace.php`
- Melhorada validação do endpoint no `SqsClient` para evitar erros quando não fornecido
- Adicionada verificação se `mime_content_type()` existe antes de usá-la no `S3.php`
- Corrigidos todos os usos de `Exception` sem namespace nos seguintes arquivos:
  - `src/helpers.php`
  - `src/Action/ImAliveAction.php`
  - `src/Commands/MigrateCreateCommand.php`
  - `src/Commands/MigrateRunCommand.php`
  - `src/Utils/Crypt.php`

### Changed
- AWS SDK PHP atualizado de `3.324.0` para `^3.350`
- Melhorada configuração do SqsClient para ser mais robusta
- Adicionadas verificações de segurança para endpoints opcionais

### Security
- Melhorada validação de entrada de dados
- Adicionadas verificações de segurança para configurações AWS

## [2.1.0] - Versão anterior

### Added
- Funcionalidades iniciais do framework
- Suporte a módulos
- Sistema de migrações
- Integração com AWS S3 e SQS
- Sistema de autenticação e criptografia
- Middlewares personalizados 