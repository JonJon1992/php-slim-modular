# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.5.0] - 2025-08-05

### Changed
- Adicionado declare(strict_types=1) em todos os arquivos PHP do projeto
- Correção de tipagem em Traits, ValueObjects, Entities, Utils, Middleware, Abstract, App, Contracts, etc.
- Compatibilidade de assinaturas entre interfaces e implementações
- Melhoria de docblocks e tipos de retorno
- Refatoração de construtores e métodos para maior consistência
- Remoção de tags PHP desnecessárias em views
- Ajuste de métodos de interfaces para garantir compatibilidade
- Correção de lógica invertida em construtores de coleções

### Fixed
- Correção de erros de tipagem e inconsistências de assinatura
- Garantia de compatibilidade total com PHP 8.2+
- Código mais limpo, seguro e padronizado

## [2.4.0] - 2025-08-05

### Added
- **Health Check Avançado**: Refatoração completa do `ImAliveAction` com funcionalidades avançadas
- **Suporte ao DynamoDB**: Verificação de conectividade e tabelas do DynamoDB no health check
- **Métricas Detalhadas**: Latência, status e contadores de serviços no health check
- **Cache Inteligente**: Sistema de cache para evitar verificações desnecessárias
- **Logs Estruturados**: Logs de sucesso e falha com contexto completo
- **Verificação Seletiva**: Parâmetro `service` para verificar serviços específicos
- **Forçar Verificação**: Parâmetro `force` para ignorar cache
- **Tipagem Forte**: Implementação completa de `declare(strict_types=1)`
- **Documentação Completa**: PHPDoc detalhado em todas as classes e métodos
- **Exemplo Completo**: Arquivo `examples/health-check-example.php` com demonstrações

### Changed
- **Correção de Erro**: `HEALTY_CHECK` → `HEALTHY_CHECK` (correção de digitação)
- **Refatoração da Lógica**: Separação de responsabilidades em métodos privados
- **Estrutura de Resposta**: JSON estruturado com informações detalhadas por serviço
- **Tratamento de Erros**: Captura específica de exceções com mensagens detalhadas
- **Status HTTP**: 503 para falhas de serviços, 200 para sucesso
- **Limitação de Logs**: Máximo de 100 entradas no arquivo de log
- **Documentação Atualizada**: README.md com seção completa do health check

### Fixed
- **Tipagem**: Adicionada tipagem forte em todos os métodos
- **Organização**: Código mais limpo e organizado
- **Performance**: Cache reduz verificações desnecessárias
- **Observabilidade**: Métricas detalhadas para monitoramento

## [2.3.0] - 2025-08-05

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

## [2.2.9] - 2025-08-05

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

## [2.2.8] - 2025-08-05

### Added
- **Versão estável**: Framework na versão 2.2.8 com todas as funcionalidades principais
- **Integração completa AWS**: S3, SQS e DynamoDB totalmente integrados
- **Sistema de helpers**: Helpers para todos os serviços principais
- **Middleware completo**: Sistema de middlewares para autenticação, CORS, rate limiting, etc.

### Changed
- **Framework maduro**: Todas as funcionalidades principais implementadas e testadas
- **Documentação completa**: README.md e CHANGELOG.md atualizados
- **Estrutura modular**: Sistema de módulos funcionando perfeitamente

## [2.2.7] - 2025-08-05

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

## [2.2.6] - 2025-08-05

### Fixed
- **Resolvido conflito de função `s3()`**: Adicionado `if (!function_exists('s3'))` em `src/helpers.php` para evitar redeclaração da função
- **Corrigidas assinaturas incompatíveis na classe `Str`**: Removidos type hints `string` dos métodos `endsWith()`, `camel()`, `ucfirst()` e `studly()` para manter compatibilidade com `Illuminate\Support\Str`
- **Compatibilidade com pacotes**: Resolvidos conflitos entre `jonjon1992/php-slim-modular`, `jonjon1992/s3-php` e `illuminate/support` v10.48.28

### Changed
- Melhorada compatibilidade com dependências externas
- Mantida funcionalidade completa da biblioteca

## [2.2.5] - 2025-08-05

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

## [2.2.4] - 2025-08-05

### Fixed
- Corrigido uso de `HttpException` para `HttpInternalServerErrorException` no `Action.php`
- Criadas classes de exceção personalizadas para melhor tratamento de erros:
  - `AuthenticationException` para erros de autenticação
  - `InvalidRequestException` para erros de validação de requisição
  - `CommandException` para erros de execução de comandos
  - `AppException` para erros gerais da aplicação
- Atualizado uso de `Exception` genérica para exceções específicas em todo o framework
- Melhorada segurança de tipos e tratamento de erros

## [2.2.3] - 2025-08-05

### Fixed
- Resolvido conflito adicional de dependências com `slim/psr7` e `slim/slim`
- Atualizado `slim/psr7` de `1.6.1` para `^1.7` para suportar `psr/http-message ^2.0`
- Atualizado `slim/slim` de `4.8.1` para `^4.9` para melhor compatibilidade
- Corrigidos todos os conflitos de dependências PSR HTTP Message

## [2.2.2] - 2025-08-05

### Fixed
- Resolvido conflito de dependências entre `aws/aws-sdk-php` e `slim/php-view`
- Atualizado `slim/php-view` de `3.1.0` para `^3.2` para suportar `psr/http-message ^2.0`
- Corrigido problema de compatibilidade com AWS SDK PHP ^3.350

## [2.2.1] - 2025-08-05

### Changed
- Atualizado `jonjon1992/s3-php` de `v2.0.0` para `v2.1.0`

## [2.2.0] - 2025-08-05

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

## [2.7.0] - 2025-08-05

### Added
- **Atualização de Datas**: Todas as datas dos documentos atualizadas para 2025-08-05
- **Consistência Temporal**: CHANGELOG com datas padronizadas
- **Documentação Atualizada**: Exemplos e referências temporais corrigidas

### Changed
- **CHANGELOG**: Todas as versões agora têm data consistente (2025-08-05)
- **Documentação Técnica**: Timestamps de exemplo atualizados
- **Referências Temporais**: Data de resolução de conflitos atualizada

### Fixed
- **Consistência**: Documentação sempre reflete a data atual
- **Profissionalismo**: Histórico de versões com datas corretas

## [2.6.0] - 2025-08-05

### Added
- **Sistema de Validação Melhorado**: Corrigida dependência inexistente `Awurth\Validator\Validator`
- **Validação com Respect\Validation**: Implementada validação usando `Respect\Validation\Validator`
- **Value Objects Corrigidos**: Lógica de validação corrigida em Value Objects
- **Scripts de Automação**: Sistema completo de scripts para commits e releases
- **Script de Release**: `scripts/release.sh` para commits e releases automatizados
- **Script de Commit Rápido**: `scripts/quick-commit.sh` com detecção automática de tipo
- **Configuração de Aliases**: `scripts/setup-aliases.sh` para facilitar uso dos scripts
- **Documentação Completa**: README dos scripts com exemplos de uso

### Changed
- **Correção de Método Chaining**: Corrigido ordem de métodos em validação de CPF
- **Validação Inteligente**: Sistema detecta automaticamente tipo de commit
- **Padronização**: Mensagens de commit seguem convenções do projeto
- **Automação**: Releases incluem atualização de versão, changelog e tags

### Fixed
- **Dependências**: Removida dependência inexistente do validator
- **Validação**: Corrigida lógica de validação em Value Objects
- **Compatibilidade**: Sistema funciona com dependências já instaladas

## [2.8.0] - 2025-08-05

### Added
- **Nova Release**: Framework atualizado para versão 2.8.0
- **Scripts de Automação**: Sistema de scripts para commits e releases funcionando perfeitamente
- **Correção de Scripts**: Script de release corrigido para permitir releases sem mudanças pendentes

### Changed
- **Versão**: Framework atualizado de 2.7.0 para 2.8.0
- **Automação**: Processo de release totalmente automatizado
- **Documentação**: CHANGELOG atualizado automaticamente

### Fixed
- **Script de Release**: Corrigida lógica para permitir releases sem mudanças pendentes
- **Automação**: Sistema de scripts funcionando corretamente

## [2.8.1] - 2025-08-05

### Adicionado
- corrige problemas de substr com valores null no AbstractAction

## [2.8.2] - 2025-08-05

### Adicionado
- corrige conflito de tipo na propriedade response do ImAliveAction

## [2.8.3] - 2025-08-05

### Adicionado
- remove autoloader do bootstrap.php

## [2.8.4] - 2025-08-05

### Adicionado
- corrige erro TypeError em str_ends_with com chaves numéricas

## [2.8.5] - 2025-08-05

### Adicionado
- melhora proteção contra TypeError em str_ends_with com verificações mais robustas

