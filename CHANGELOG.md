# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.9.1] - 2025-08-06

### Fixed
- **Erro de Tipo no Crypt**: Corrigido erro "Return value must be of type string, int returned" no método `encode()` da classe `Crypt`
- **Hashids Integration**: Forçada conversão para string no retorno do método `encode()` para garantir compatibilidade com a interface `ICrypt`
- **Conflito de Funções**: Corrigido conflito de declaração das funções `dd()` e `d()` com Symfony VarDumper
- **Dependências**: Instalada extensão `php8.2-bcmath` para suporte completo ao Hashids
- **Compatibilidade**: Framework agora funciona corretamente com valores numéricos na criptografia

### Changed
- **Método encode()**: Agora sempre retorna string conforme especificado na interface
- **Funções de Debug**: Adicionada verificação `function_exists()` para evitar conflitos
- **Tipagem**: Garantida tipagem correta em toda a cadeia de criptografia

## [2.9.0] - 2025-08-05

### Changed
- **Sistema de Exceções**: Melhorada ordem de carregamento do sistema de exceções no bootstrap.php

## [2.8.9] - 2025-08-05

### Added
- **Funções de Debug**: Adicionadas funções `dd()` e `d()` para debug similar ao Laravel
- **Compatibilidade**: Verificação `function_exists()` para evitar conflitos com Symfony VarDumper

## [2.8.8] - 2025-08-05

### Fixed
- **Conflito com Laravel**: Corrigido conflito de função `env()` com Laravel/Illuminate
- **Compatibilidade**: Renomeada função `env()` para `getEnvVar()` no exception.php
- **Erro de Redeclaração**: Corrigido erro 'Cannot redeclare env()' em projetos que usam Laravel

## [2.8.7] - 2025-08-05

### Fixed
- **Conflito com Laravel**: Corrigido conflito de função `env()` com Laravel/Illuminate
- **Compatibilidade**: Renomeada função `env()` para `getEnvVar()` no exception.php
- **Erro de Redeclaração**: Corrigido erro 'Cannot redeclare env()' em projetos que usam Laravel
- **Integração**: Framework agora é compatível com projetos Laravel sem conflitos

### Changed
- **Função env()**: Renomeada para `getEnvVar()` para evitar conflitos
- **Compatibilidade**: Melhor integração com outros frameworks PHP

## [2.8.6] - 2025-08-05

### Fixed
- **Sistema de Tratamento de Exceções**: Corrigido carregamento do sistema de exceções no bootstrap.php
- **Mensagens de Erro**: Corrigido problema onde mensagens de erro não apareciam nas requisições
- **ErrorHandler**: Corrigida propriedade indefinida `$response` no ErrorHandler
- **Debug Info**: Melhorado tratamento de informações de debug no ambiente de desenvolvimento
- **Propriedades Indefinidas**: Removidas referências a propriedades inexistentes no ErrorHandler

### Changed
- **Bootstrap**: Adicionado carregamento automático do sistema de exceções
- **ErrorHandler**: Refatorado para usar dados de erro já calculados
- **Tratamento de Erros**: Sistema mais robusto e confiável

## [2.8.5] - 2025-08-05

### Fixed
- **Proteção contra TypeError**: Melhorada proteção contra TypeError em `str_ends_with` com verificações mais robustas

## [2.8.4] - 2025-08-05

### Fixed
- **TypeError em str_ends_with**: Corrigido erro TypeError em `str_ends_with` com chaves numéricas

## [2.8.3] - 2025-08-05

### Changed
- **Bootstrap**: Removido autoloader do bootstrap.php para melhor organização

## [2.8.2] - 2025-08-05

### Fixed
- **Conflito de Tipo**: Corrigido conflito de tipo na propriedade `response` do ImAliveAction

## [2.8.1] - 2025-08-05

### Fixed
- **Problemas com substr**: Corrigidos problemas de `substr` com valores null no AbstractAction

## [2.8.0] - 2025-08-05

### Added
- **Scripts de Automação**: Sistema de scripts para commits e releases funcionando perfeitamente

### Changed
- **Versão**: Framework atualizado de 2.7.0 para 2.8.0
- **Automação**: Processo de release totalmente automatizado

### Fixed
- **Script de Release**: Corrigida lógica para permitir releases sem mudanças pendentes

## [2.7.0] - 2025-08-05

### Changed
- **Atualização de Datas**: Todas as datas dos documentos atualizadas para 2025-08-05
- **Consistência Temporal**: CHANGELOG com datas padronizadas

## [2.6.0] - 2025-08-05

### Added
- **Sistema de Validação Melhorado**: Corrigida dependência inexistente `Awurth\Validator\Validator`
- **Validação com Respect\Validation**: Implementada validação usando `Respect\Validation\Validator`
- **Scripts de Automação**: Sistema completo de scripts para commits e releases
- **Script de Release**: `scripts/release.sh` para commits e releases automatizados
- **Script de Commit Rápido**: `scripts/quick-commit.sh` com detecção automática de tipo
- **Configuração de Aliases**: `scripts/setup-aliases.sh` para facilitar uso dos scripts

### Changed
- **Correção de Método Chaining**: Corrigido ordem de métodos em validação de CPF
- **Validação Inteligente**: Sistema detecta automaticamente tipo de commit
- **Padronização**: Mensagens de commit seguem convenções do projeto

### Fixed
- **Dependências**: Removida dependência inexistente do validator
- **Validação**: Corrigida lógica de validação em Value Objects

## [2.5.0] - 2025-08-05

### Changed
- **Tipagem Forte**: Adicionado `declare(strict_types=1)` em todos os arquivos PHP do projeto
- **Correção de Tipagem**: Correção de tipagem em Traits, ValueObjects, Entities, Utils, Middleware, Abstract, App, Contracts, etc.
- **Compatibilidade de Assinaturas**: Compatibilidade de assinaturas entre interfaces e implementações
- **Melhoria de Docblocks**: Melhoria de docblocks e tipos de retorno
- **Refatoração de Construtores**: Refatoração de construtores e métodos para maior consistência
- **Remoção de Tags PHP**: Remoção de tags PHP desnecessárias em views
- **Ajuste de Métodos**: Ajuste de métodos de interfaces para garantir compatibilidade
- **Correção de Lógica**: Correção de lógica invertida em construtores de coleções

### Fixed
- **Erros de Tipagem**: Correção de erros de tipagem e inconsistências de assinatura
- **Compatibilidade PHP 8.2+**: Garantia de compatibilidade total com PHP 8.2+
- **Código Limpo**: Código mais limpo, seguro e padronizado

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

### Changed
- **Atualização de Dependências**: Atualizado `jonjon1992/s3-php` de v2.1.0 para v2.4.0
- **Melhorias de Compatibilidade**: Suporte a novas funcionalidades do S3 PHP
- **Otimizações de Performance**: Melhorias gerais no framework

## [2.2.9] - 2025-08-05

### Added
- **Melhorias na Documentação**: Atualização completa da documentação do projeto
- **Organização de Código**: Reestruturação e limpeza do código base
- **Preparação para Testes**: Estrutura preparada para implementação de testes unitários

### Changed
- **Versão Atualizada**: Framework atualizado para versão 2.2.9
- **Documentação Aprimorada**: README.md e CHANGELOG.md atualizados

## [2.2.8] - 2025-08-05

### Added
- **Versão Estável**: Framework na versão 2.2.8 com todas as funcionalidades principais
- **Integração Completa AWS**: S3, SQS e DynamoDB totalmente integrados
- **Sistema de Helpers**: Helpers para todos os serviços principais
- **Middleware Completo**: Sistema de middlewares para autenticação, CORS, rate limiting, etc.

### Changed
- **Framework Maduro**: Todas as funcionalidades principais implementadas e testadas
- **Documentação Completa**: README.md e CHANGELOG.md atualizados
- **Estrutura Modular**: Sistema de módulos funcionando perfeitamente

## [2.2.7] - 2025-08-05

### Added
- **Novo Helper DynamoDB**: Implementado helper `dynamo()` em `src/helpers.php` seguindo o padrão dos outros helpers
- **Integração com Sistema de Serviços**: Adicionado suporte para configurações do DynamoDB em `src/services.php`
- **Suporte a Múltiplas Configurações**: Helper suporta alias para diferentes ambientes (local, produção, etc.)
- **Documentação Específica**: Criado `docs/utils/dynamo-helper.md` com documentação completa do helper
- **Exemplo de Uso**: Atualizado `examples/dynamo-example.php` com exemplos do helper
- **Testes Unitários**: Criado `tests/Utils/DynamoHelperTest.php` para validar o helper

### Changed
- **Padrão Consistente**: Helper `dynamo()` segue o mesmo padrão dos outros helpers (redis, s3, db, etc.)
- **Integração com DI**: Helper integra automaticamente com o sistema de injeção de dependência
- **Configuração Flexível**: Suporte a variáveis de ambiente e configurações customizadas

### Fixed
- **Consistência de Helpers**: Todos os helpers agora seguem o mesmo padrão de implementação

## [2.2.6] - 2025-08-05

### Fixed
- **Conflito de Função s3()**: Adicionado `if (!function_exists('s3'))` em `src/helpers.php` para evitar redeclaração da função
- **Assinaturas Incompatíveis**: Corrigidas assinaturas incompatíveis na classe `Str` removendo type hints `string` dos métodos `endsWith()`, `camel()`, `ucfirst()` e `studly()`
- **Compatibilidade com Pacotes**: Resolvidos conflitos entre `jonjon1992/php-slim-modular`, `jonjon1992/s3-php` e `illuminate/support` v10.48.28

### Changed
- **Compatibilidade**: Melhorada compatibilidade com dependências externas

## [2.2.5] - 2025-08-05

### Added
- **Novo Util DynamoDB**: Implementado `Core\Utils\Dynamo` para integração com Amazon DynamoDB
- **Suporte Completo CRUD**: Suporte completo para operações CRUD no DynamoDB
- **Configuração Automática**: Configuração automática para ambiente local (LocalStack) e produção (AWS)
- **Operações em Lote**: Operações em lote (batch operations)
- **Queries e Scans**: Suporte a queries e scans
- **Gerenciamento de Tabelas**: Gerenciamento de tabelas (criar, listar, descrever, remover)
- **Conversão Flexível**: Conversão flexível de dados (objetos, arrays, stdClass)
- **Documentação Completa**: Documentação completa em `docs/utils/dynamo.md`
- **Exemplo de Uso**: Exemplo de uso em `examples/dynamo-example.php`
- **Integração**: Integração com a package `jonjon1992/php-dynamo` v3.0.2

### Changed
- **README Atualizado**: Atualizado README.md com seção de uso do DynamoDB
- **Documentação**: Melhorada documentação de utils do framework

## [2.2.4] - 2025-08-05

### Fixed
- **HttpException**: Corrigido uso de `HttpException` para `HttpInternalServerErrorException` no `Action.php`
- **Classes de Exceção**: Criadas classes de exceção personalizadas para melhor tratamento de erros:
  - `AuthenticationException` para erros de autenticação
  - `InvalidRequestException` para erros de validação de requisição
  - `CommandException` para erros de execução de comandos
  - `AppException` para erros gerais da aplicação
- **Exceções Específicas**: Atualizado uso de `Exception` genérica para exceções específicas em todo o framework

### Changed
- **Segurança de Tipos**: Melhorada segurança de tipos e tratamento de erros

## [2.2.3] - 2025-08-05

### Fixed
- **Conflito de Dependências**: Resolvido conflito adicional de dependências com `slim/psr7` e `slim/slim`
- **Atualização slim/psr7**: Atualizado `slim/psr7` de `1.6.1` para `^1.7` para suportar `psr/http-message ^2.0`
- **Atualização slim/slim**: Atualizado `slim/slim` de `4.8.1` para `^4.9` para melhor compatibilidade
- **Conflitos PSR**: Corrigidos todos os conflitos de dependências PSR HTTP Message

## [2.2.2] - 2025-08-05

### Fixed
- **Conflito AWS SDK**: Resolvido conflito de dependências entre `aws/aws-sdk-php` e `slim/php-view`
- **Atualização slim/php-view**: Atualizado `slim/php-view` de `3.1.0` para `^3.2` para suportar `psr/http-message ^2.0`
- **Compatibilidade AWS**: Corrigido problema de compatibilidade com AWS SDK PHP ^3.350

## [2.2.1] - 2025-08-05

### Changed
- **Atualização S3 PHP**: Atualizado `jonjon1992/s3-php` de `v2.0.0` para `v2.1.0`

## [2.2.0] - 2025-08-05

### Added
- **AWS SDK PHP**: AWS SDK PHP atualizado para versão ^3.350
- **Helper S3**: Função helper `s3()` para acessar buckets S3
- **Helper Queue**: Função helper `queue()` para acessar clientes SQS
- **Métodos String**: Métodos `endsWith()`, `camel()`, `ucfirst()` e `studly()` na classe `Str`

### Fixed
- **Exception sem Namespace**: Corrigido uso de `Exception` sem namespace em múltiplos arquivos
- **Propriedade Não Utilizada**: Removida propriedade `$client` não utilizada no `SqsClient`
- **Variável Não Definida**: Corrigida variável `$service` não definida no `ImAliveAction`
- **Import da Classe Str**: Adicionado import da classe `Str` no `Trace.php`
- **Validação de Endpoint**: Melhorada validação do endpoint no `SqsClient` para evitar erros quando não fornecido
- **Verificação mime_content_type**: Adicionada verificação se `mime_content_type()` existe antes de usá-la no `S3.php`
- **Exception sem Namespace**: Corrigidos todos os usos de `Exception` sem namespace nos seguintes arquivos:
  - `src/helpers.php`
  - `src/Action/ImAliveAction.php`
  - `src/Commands/MigrateCreateCommand.php`
  - `src/Commands/MigrateRunCommand.php`
  - `src/Utils/Crypt.php`

### Changed
- **AWS SDK PHP**: AWS SDK PHP atualizado de `3.324.0` para `^3.350`
- **Configuração SqsClient**: Melhorada configuração do SqsClient para ser mais robusta
- **Verificações de Segurança**: Adicionadas verificações de segurança para endpoints opcionais

### Security
- **Validação de Entrada**: Melhorada validação de entrada de dados
- **Verificações de Segurança**: Adicionadas verificações de segurança para configurações AWS

## [2.1.0] - 2025-08-05

### Added
- **Funcionalidades Iniciais**: Funcionalidades iniciais do framework
- **Sistema de Módulos**: Suporte a módulos
- **Sistema de Migrações**: Sistema de migrações
- **Integração AWS**: Integração com AWS S3 e SQS
- **Sistema de Autenticação**: Sistema de autenticação e criptografia
- **Middlewares Personalizados**: Middlewares personalizados

