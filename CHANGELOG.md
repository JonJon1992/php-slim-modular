# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.3.0] - 2025-08-04

### Added
- **Atualização de dependências**: Atualizado `jonjon1992/s3-php` de v2.1.0 para v2.4.0
- **Melhorias de compatibilidade**: Suporte a novas funcionalidades do S3 PHP
- **Otimizações de performance**: Melhorias gerais no framework

### Changed
- **Versão atualizada**: Framework atualizado para versão 2.3.0
- **Dependências atualizadas**: Composer.lock atualizado com novas versões
- **Estrutura de projeto**: Melhor organização dos arquivos e diretórios

### Fixed
- **Consistência de código**: Padronização de estilo e formatação
- **Documentação**: Correções e melhorias na documentação existente

## [2.2.9] - 2024-12-19

### Added
- **Melhorias na documentação**: Atualização completa da documentação do projeto
- **Organização de código**: Reestruturação e limpeza do código base
- **Preparação para testes**: Estrutura preparada para implementação de testes unitários
- **Otimizações de performance**: Melhorias gerais no framework

### Changed
- **Versão atualizada**: Framework atualizado para versão 2.2.9
- **Documentação aprimorada**: README.md e CHANGELOG.md atualizados
- **Estrutura de projeto**: Melhor organização dos arquivos e diretórios

### Fixed
- **Consistência de código**: Padronização de estilo e formatação
- **Documentação**: Correções e melhorias na documentação existente

## [2.2.8] - 2024-12-19

### Added
- **Versão estável**: Framework na versão 2.2.8 com todas as funcionalidades principais
- **Integração completa AWS**: S3, SQS e DynamoDB totalmente integrados
- **Sistema de helpers**: Helpers para todos os serviços principais
- **Middleware completo**: Sistema de middlewares para autenticação, CORS, rate limiting, etc.

### Changed
- **Framework maduro**: Todas as funcionalidades principais implementadas e testadas
- **Documentação completa**: README.md e CHANGELOG.md atualizados
- **Estrutura modular**: Sistema de módulos funcionando perfeitamente

## [2.2.7] - 2024-12-19

### Added
- **Novo helper DynamoDB**: Implementado helper `dynamo()` em `src/helpers.php` seguindo o padrão dos outros helpers
- **Integração com sistema de serviços**: Adicionado suporte para configurações do DynamoDB em `src/services.php`
- **Suporte a múltiplas configurações**: Helper suporta alias para diferentes ambientes (local, produção, etc.)
- **Documentação específica**: Criado `docs/utils/dynamo-helper.md` com documentação completa do helper
- **Exemplo de uso**: Atualizado `examples/dynamo-example.php` com exemplos do helper
- **Testes unitários**: Criado `tests/Utils/DynamoHelperTest.php` para validar o helper

### Changed
- **Padrão consistente**: Helper `dynamo()` segue o mesmo padrão dos outros helpers (redis, s3, db, etc.)
- **Integração com DI**: Helper integra automaticamente com o sistema de injeção de dependência
- **Configuração flexível**: Suporte a variáveis de ambiente e configurações customizadas

### Fixed
- **Consistência de helpers**: Todos os helpers agora seguem o mesmo padrão de implementação
- **Documentação atualizada**: @TODO atualizado com detalhes da implementação do helper

## [2.2.6] - 2024-08-03

### Fixed
- **Resolvido conflito de função `s3()`**: Adicionado `if (!function_exists('s3'))` em `src/helpers.php` para evitar redeclaração da função
- **Corrigidas assinaturas incompatíveis na classe `Str`**: Removidos type hints `string` dos métodos `endsWith()`, `camel()`, `ucfirst()` e `studly()` para manter compatibilidade com `Illuminate\Support\Str`
- **Compatibilidade com pacotes**: Resolvidos conflitos entre `jonjon1992/php-slim-modular`, `jonjon1992/s3-php` e `illuminate/support` v10.48.28

### Changed
- Melhorada compatibilidade com dependências externas
- Mantida funcionalidade completa da biblioteca

## [2.2.5] - 2024-12-19

### Added
- **Novo util DynamoDB**: Implementado `Core\Utils\Dynamo` para integração com Amazon DynamoDB
- Suporte completo para operações CRUD no DynamoDB
- Configuração automática para ambiente local (LocalStack) e produção (AWS)
- Operações em lote (batch operations)
- Suporte a queries e scans
- Gerenciamento de tabelas (criar, listar, descrever, remover)
- Conversão flexível de dados (objetos, arrays, stdClass)
- Documentação completa em `docs/utils/dynamo.md`
- Exemplo de uso em `examples/dynamo-example.php`
- Integração com a package `jonjon1992/php-dynamo` v3.0.2

### Changed
- Atualizado README.md com seção de uso do DynamoDB
- Melhorada documentação de utils do framework

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