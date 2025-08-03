# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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