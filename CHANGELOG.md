# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Versionamento Semântico](https://semver.org/lang/pt-BR/).

## [Não Lançado]

## [2.10.4] - 2026-02-01

### Corrigido

- **QueueServiceTrait**: adicionado `setDefaultConnection()` ao QueueManager
  - Resolvido erro: "Call to a member function getConnection() on null" no Worker
  - Cada instância do QueueManager agora tem sua conexão padrão configurada corretamente
  - Worker consegue acessar a conexão através do QueueManager

### Adicionado

- **Default connection**: agora cada QueueManager tem sua conexão configurada como padrão via `$capsule->getQueueManager()->setDefaultConnection($alias)`

## [2.10.3] - 2026-02-01

### Corrigido

- **QueueServiceTrait**: corrigido retorno do service binding para retornar `QueueManager` ao invés de `getConnection()`
  - Resolvido erro: "Call to a member function getConnection() on null"
  - O QueueManager agora gerencia as conexões corretamente
  - Worker pode acessar as conexões através do manager

### Alterado

- **Return type**: alterado de `$capsule->getConnection($alias)` para `$capsule` no binding da queue

## [2.10.2] - 2026-02-01

### Corrigido

- **QueueServiceTrait**: corrigida implementação do binding `db` para implementar `ConnectionResolverInterface`
  - Adicionados métodos `getDefaultConnection()` e `setDefaultConnection()`
  - Resolvido erro: "Argument #1 ($connections) must be of type ConnectionResolverInterface, class@anonymous given"
- **DatabaseConnector**: agora funciona corretamente com a classe anônima que implementa a interface correta

### Alterado

- **Classe anônima do db**: agora implementa `Illuminate\Database\ConnectionResolverInterface` adequadamente

## [2.10.1] - 2026-02-01

### Corrigido

- **QueueWorkCommand**: corrigido comando `queue:work` para usar estrutura modular `queue.{module}.{connection}`
- **Helper queue()**: atualizado helper `queue()` para suportar a nova estrutura modular
  - Agora aceita parâmetros `$module` e `$connection`
  - Usa módulo padrão de `queue.default` quando não especificado
  - Usa conexão `database` como padrão quando não especificada
- **Erro "No entry or class found for 'queue'"**: resolvido erro ao executar `queue:work`

### Adicionado

- **Opção --module**: adicionada opção `--module` no comando `queue:work` para especificar o módulo
- **Documentação**: criado guia completo de uso em `docs/QUEUE_USAGE.md` com exemplos práticos
- **Validações**: adicionada validação para garantir que módulo padrão está configurado

### Alterado

- **queue:work**: opção `--connection` agora refere-se ao tipo de conexão (sync, database, redis, sqs) ao invés do nome da conexão
- **Conexão padrão**: alterada de `default` para `database` no comando queue:work

## [2.10.0] - 2026-02-01

### Adicionado

- **Service Traits**: criadas 8 traits modulares para organização de serviços em `src/Traits/Services/`:
  - `LogServiceTrait` - gerenciamento de serviços de log (Monolog)
  - `DatabaseServiceTrait` - gerenciamento de conexões de banco de dados (Eloquent)
  - `CacheServiceTrait` - gerenciamento de serviços de cache (Redis)
  - `StorageServiceTrait` - gerenciamento de serviços de storage (S3)
  - `QueueServiceTrait` - gerenciamento de serviços de queue (Laravel Queue)
  - `DynamoServiceTrait` - gerenciamento de serviços de DynamoDB
  - `ViewServiceTrait` - gerenciamento de serviços de view/render
  - `MailerServiceTrait` - gerenciamento de serviços de email (SMTP)
- **Documentação**: criado `src/Traits/Services/README.md` com guia completo de uso das traits
- **Queue Modular**: implementado suporte a múltiplos módulos de queue com estrutura aninhada

### Refatorado

- **services.php**: refatorado completamente de ~156 linhas para ~47 linhas usando traits modulares
- **Queue System**: ajustado sistema de queue para funcionar por módulos, seguindo padrão dos outros serviços
  - Estrutura de configuração agora suporta múltiplos módulos (ex: `ms-keys`, `outro-modulo`)
  - Cada módulo tem suas próprias conexões (sync, database, redis, sqs)
  - Bindings no formato: `queue.{module}.{connection}` (ex: `queue.ms-keys.database`)
- **Organização**: melhor separação de responsabilidades com cada trait gerenciando um tipo de serviço
- **Manutenibilidade**: código mais limpo, organizado e fácil de manter

### Alterado

- **configs/queue.php**: atualizado para estrutura modular aninhada com suporte a múltiplos módulos
- **Padrão de código**: todos os serviços agora seguem o mesmo padrão modular consistente

## [2.9.18] - 2026-02-01

### Corrigido

- Namespaces: corrigidos imports incorretos em `helpers.php` e `ExceptionHandler.php`
- Imports: movidos imports de `Core\Traits` para `Core\Utils` para classes: `Auth`, `Client`, `Config`, `Dynamo`, `Env`, `Path`, `Redis`, `SqsClient`, `Trace`
- Exception: corrigida referência ao `Exception` global com prefixo `\` em `helpers.php`
- IRendebleAdapter: movida de `Core\Traits\Render` para `Core\Utils\Render` para melhor organização
- SlimPhpRenderAdapter: atualizado namespace de `Core\Traits\Render` para `Core\Utils\Render`
- Autoload: corrigidos erros "Class not found" para `Core\Traits\Path` e `Core\Traits\Trace`
- PHP Warnings: removido warning "The use statement with non-compound name 'Exception' has no effect"

### Refatorado

- Organização de código: melhor organização de classes entre `Core\Traits` e `Core\Utils`
- Namespace consistency: todos os utils agora estão corretamente no namespace `Core\Utils`
- Code cleanup: removidos comentários redundantes e espaços em branco desnecessários em `Config.php`

## [2.9.17] - 2026-02-01

### Refatorado

- Migração de Traits para Utils: movidas classes `Env`, `Path` e `Trace` de traits para utils
- ErrorHandler: atualizado para usar `Core\Utils\Env` ao invés de `Core\Traits\Env`
- Bootstrap: atualizado para usar `Core\Utils\Path` e `Core\Utils\Trace`
- Limpeza: removidos comentários redundantes no ErrorHandler

## [2.9.16] - 2026-02-01

### Refatorado

- Clients AWS: extraída lógica comum de inicialização para trait `AwsClientBase`
- SnsClient: refatorado para usar trait `AwsClientBase`, eliminando código duplicado
- SqsClient: refatorado para usar trait `AwsClientBase`, eliminando código duplicado
- DRY: reduzido duplicação de código entre clients AWS (de ~30 linhas duplicadas para trait reutilizável)
- Manutenibilidade: mudanças futuras em configuração AWS agora centralizadas em um único local

## [2.9.15] - 2026-02-01

### Refatorado

- Refatoração: Melhorias na tipagem e limpeza de código
- Type hinting aprimorado em diversos métodos
- Remoção de código não utilizado e comentários redundantes
- Ajustes em `bootstrap.php` e `helpers.php`

## [2.9.14] - 2026-01-28

### Corrigido

- ExceptionHandler: corrigido erro de tipo "Return value must be of type int, string returned" no método `getHttpStatusCode()`
- Conversão de tipos: adicionada conversão explícita `(int)` para `getCode()` garantir retorno de inteiro
- Simplificação: removida verificação de método `getStatusCode()` desnecessária

## [2.9.13] - 2026-01-27

### Corrigido

- CreateClientCommand: corrigido nome da tabela de inserção de `migrations` para `module_client_keys`

## [2.9.12] - 2026-01-27

### Segurança

- Dependências: relaxada restrição de versão do `phpunit/phpunit` para `^10.5` para permitir correção de segurança ("PKSA-z3gr-8qht-p93v")

### Alterado

- Composer: removidos campos `homepage` e `support` do `composer.json`

## [2.9.11] - 2026-01-27

### Alterado

- Atualizado `aws/aws-sdk-php` para versão `3.368.0.0`

## [2.9.10] - 2026-01-27

### Corrigido

- Bootstrap: corrigido carregamento de arquivo de configuração para `cors.php` ao invés de `config.php` em `src/bootstrap.php`.

## [2.9.9] - 2026-01-27

### Melhorado

- Lógica do Redis `getOrSet`: implementação de verificação estrita (`!== false`) para evitar falsos negativos, melhor tratamento de expiração e segurança contra falhas no `json_encode`.

### Refatorado

- Limpeza de código: remoção de comentários redundantes e simplificação de sintaxe em `bootstrap.php`, `exception.php`, `Dynamo.php` e `Utils/Redis.php`.

## [2.9.7] - 2025-01-27

### Alterado

- Implementação de conexão Redis melhorada com método `connectInternal()` separado e correção de autenticação
- Scrambler aprimorado com `declare(strict_types=1)`, substituído `rand()` por `random_int()` e melhorada legibilidade do código
- Melhorias no Serializable: substituído `is_null()` por `=== null`, `==` por `===` e melhorada validação de tipos
- Geração de código Str: substituído `rand()` por `random_int()` e simplificado cálculo do limite no método `code()`
- Validação de tempo: melhorado método `validate()` com conversão explícita de tipos e variáveis renomeadas
- Otimização de rotas: removido parâmetro não utilizado e adicionados type hints nos callbacks
- Refatoração de serviços: substituído `==` por `===` em todas as comparações e removidos parâmetros não utilizados

### Corrigido

- Autenticação Redis: corrigida chamada de autenticação para usar `$this->redis->auth()` na instância correta
- Uso de `is_null()`: removido uso de `is_null()` em favor de `=== null` para melhor performance e otimização do compilador
- Comparações de tipos: corrigidas todas as comparações para usar operador estrito `===` ao invés de `==`
- Parâmetros não utilizados: removidos parâmetros não utilizados em closures e métodos

## [2.9.6] - 2025-09-26

### Adicionado

- Aprimoramento de ValueObjects: adicionado método `getValue()` à interface `IValueObject` e implementação na classe base `ValueObject`
- Tratamento de exceções melhorado: melhor tratamento de exceções com import explícito da classe `Exception`

### Corrigido

- Método Str::code(): corrigido parâmetro faltante no método `code()` da classe `Str` para geração correta de códigos numéricos

### Alterado

- Interface ValueObject: interface `IValueObject` agora inclui método `getValue()` para acesso direto ao valor
- Geração de código: método `Str::code()` agora funciona corretamente com tamanhos personalizados

## [2.9.5] - 2025-08-11

### Adicionado

- Melhorias no sistema de testes e comandos de migração

## [2.9.4] - 2025-08-07

### Adicionado

- Melhorias no BaseTestCase com tipagem e organização

## [2.9.3] - 2025-08-07

### Adicionado

- Implementação do DynamoMock seguindo o padrão do RedisMock

## [2.9.2] - 2025-08-06

### Adicionado

- Melhorias no BaseTestCase: melhorias significativas no sistema de testes
- Segurança aprimorada: comandos shell seguros com `escapeshellarg()` para prevenir injection
- Tratamento robusto de erros: try-catch abrangente com logs detalhados e validações
- Permissões configuráveis: suporte para variável de ambiente `TEST_USER` para flexibilidade
- Helpers de testes: novos métodos `assertJsonResponse()`, `createTestData()`, `cleanTestData()`
- Documentação completa: guia detalhado em `docs/TESTING_IMPROVEMENTS.md`
- Exemplo prático: arquivo `examples/BaseTestCaseExample.php` com casos de uso reais
- Mock do Redis: classe `RedisMock` para testes sem dependência externa

### Alterado

- Método executeCommand(): implementado para execução segura de comandos shell
- Configuração SQLite: melhorada com validações e permissões adequadas
- Logs de debug: sistema de logging detalhado para rastreamento de operações
- Validações: verificação de sucesso para todos os comandos shell
- Permissões: definição automática de chmod apropriados para arquivos SQLite

### Corrigido

- Comandos exec: corrigidos espaços faltantes em comandos `rm -f` e `mkdir -p`
- Segurança: prevenção de command injection com `escapeshellarg()`
- Robustez: tratamento adequado de erros em operações críticas
- Flexibilidade: suporte a diferentes ambientes via configuração

## [2.9.1] - 2025-08-06

### Corrigido

- Erro de tipo no Crypt: corrigido erro "Return value must be of type string, int returned" no método `encode()` da classe `Crypt`
- Integração Hashids: forçada conversão para string no retorno do método `encode()` para garantir compatibilidade com a interface `ICrypt`
- Conflito de funções: corrigido conflito de declaração das funções `dd()` e `d()` com Symfony VarDumper
- Dependências: instalada extensão `php8.2-bcmath` para suporte completo ao Hashids
- Compatibilidade: framework agora funciona corretamente com valores numéricos na criptografia

### Alterado

- Método encode(): agora sempre retorna string conforme especificado na interface
- Funções de debug: adicionada verificação `function_exists()` para evitar conflitos
- Tipagem: garantida tipagem correta em toda a cadeia de criptografia

## [2.9.0] - 2025-08-05

### Alterado

- Sistema de exceções: melhorada ordem de carregamento do sistema de exceções no bootstrap.php

## [2.8.9] - 2025-08-05

### Adicionado

- Funções de debug: adicionadas funções `dd()` e `d()` para debug similar ao Laravel
- Compatibilidade: verificação `function_exists()` para evitar conflitos com Symfony VarDumper

## [2.8.8] - 2025-08-05

### Corrigido

- Conflito com Laravel: corrigido conflito de função `env()` com Laravel/Illuminate
- Compatibilidade: renomeada função `env()` para `getEnvVar()` no exception.php
- Erro de redeclaração: corrigido erro 'Cannot redeclare env()' em projetos que usam Laravel

## [2.8.7] - 2025-08-05

### Corrigido

- Conflito com Laravel: corrigido conflito de função `env()` com Laravel/Illuminate
- Compatibilidade: renomeada função `env()` para `getEnvVar()` para evitar conflitos
- Erro de redeclaração: corrigido erro 'Cannot redeclare env()' em projetos que usam Laravel
- Integração: framework agora é compatível com projetos Laravel sem conflitos

### Alterado

- Função env(): renomeada para `getEnvVar()` para evitar conflitos
- Compatibilidade: melhor integração com outros frameworks PHP

## [2.8.6] - 2025-08-05

### Corrigido

- Sistema de tratamento de exceções: corrigido carregamento do sistema de exceções no bootstrap.php
- Mensagens de erro: corrigido problema onde mensagens de erro não apareciam nas requisições
- ErrorHandler: corrigida propriedade indefinida `$response` no ErrorHandler
- Informações de debug: melhorado tratamento de informações de debug no ambiente de desenvolvimento
- Propriedades indefinidas: removidas referências a propriedades inexistentes no ErrorHandler

### Alterado

- Bootstrap: adicionado carregamento automático do sistema de exceções
- ErrorHandler: refatorado para usar dados de erro já calculados
- Tratamento de erros: sistema mais robusto e confiável

## [2.8.5] - 2025-08-05

### Corrigido

- Proteção contra TypeError: melhorada proteção contra TypeError em `str_ends_with` com verificações mais robustas

## [2.8.4] - 2025-08-05

### Corrigido

- TypeError em str_ends_with: corrigido TypeError em `str_ends_with` com chaves numéricas

## [2.8.3] - 2025-08-05

### Alterado

- Bootstrap: removido autoloader do bootstrap.php para melhor organização

## [2.8.2] - 2025-08-05

### Corrigido

- Conflito de tipo: corrigido conflito de tipo na propriedade `response` do ImAliveAction

## [2.8.1] - 2025-08-05

### Corrigido

- Problemas com substr: corrigidos problemas de `substr` com valores null no AbstractAction

## [2.8.0] - 2025-08-05

### Adicionado

- Scripts de automação: sistema de scripts para commits e releases funcionando perfeitamente

### Alterado

- Versão: framework atualizado de 2.7.0 para 2.8.0
- Automação: processo de release totalmente automatizado

### Corrigido

- Script de release: corrigida lógica para permitir releases sem mudanças pendentes

## [2.7.0] - 2025-08-05

### Alterado

- Atualização de datas: todas as datas dos documentos atualizadas para 2025-08-05
- Consistência temporal: CHANGELOG com datas padronizadas

## [2.6.0] - 2025-08-05

### Adicionado

- Sistema de validação melhorado: corrigida dependência inexistente `Awurth\Validator\Validator`
- Validação com Respect\Validation: implementada validação usando `Respect\Validation\Validator`
- Scripts de automação: sistema completo de scripts para commits e releases
- Script de release: `scripts/release.sh` para commits e releases automatizados
- Script de commit rápido: `scripts/quick-commit.sh` com detecção automática de tipo
- Configuração de aliases: `scripts/setup-aliases.sh` para facilitar uso dos scripts

### Alterado

- Correção de método chaining: corrigida ordem de métodos em validação de CPF
- Validação inteligente: sistema detecta automaticamente tipo de commit
- Padronização: mensagens de commit seguem convenções do projeto

### Corrigido

- Dependências: removida dependência inexistente do validator
- Validação: corrigida lógica de validação em Value Objects

## [2.5.0] - 2025-08-05

### Alterado

- Tipagem forte: adicionado `declare(strict_types=1)` em todos os arquivos PHP do projeto
- Correção de tipagem: correção de tipagem em Traits, ValueObjects, Entities, Utils, Middleware, Abstract, App, Contracts, etc.
- Compatibilidade de assinaturas: compatibilidade de assinaturas entre interfaces e implementações
- Melhoria de docblocks: melhoria de docblocks e tipos de retorno
- Refatoração de construtores: refatoração de construtores e métodos para maior consistência
- Remoção de tags PHP: remoção de tags PHP desnecessárias em views
- Ajuste de métodos: ajuste de métodos de interfaces para garantir compatibilidade
- Correção de lógica: correção de lógica invertida em construtores de coleções

### Corrigido

- Erros de tipagem: correção de erros de tipagem e inconsistências de assinatura
- Compatibilidade PHP 8.2+: garantia de compatibilidade total com PHP 8.2+
- Código limpo: código mais limpo, seguro e padronizado

## [2.4.0] - 2025-08-05

### Adicionado

- Health check avançado: refatoração completa do `ImAliveAction` com funcionalidades avançadas
- Suporte ao DynamoDB: verificação de conectividade e tabelas do DynamoDB no health check
- Métricas detalhadas: latência, status e contadores de serviços no health check
- Cache inteligente: sistema de cache para evitar verificações desnecessárias
- Logs estruturados: logs de sucesso e falha com contexto completo
- Verificação seletiva: parâmetro `service` para verificar serviços específicos
- Forçar verificação: parâmetro `force` para ignorar cache
- Tipagem forte: implementação completa de `declare(strict_types=1)`
- Documentação completa: PHPDoc detalhado em todas as classes e métodos
- Exemplo completo: arquivo `examples/health-check-example.php` com demonstrações

### Alterado

- Correção de erro: `HEALTY_CHECK` → `HEALTHY_CHECK` (correção de digitação)
- Refatoração da lógica: separação de responsabilidades em métodos privados
- Estrutura de resposta: JSON estruturado com informações detalhadas por serviço
- Tratamento de erros: captura específica de exceções com mensagens detalhadas
- Status HTTP: 503 para falhas de serviços, 200 para sucesso
- Limitação de logs: máximo de 100 entradas no arquivo de log
- Documentação atualizada: README.md com seção completa do health check

### Corrigido

- Tipagem: adicionada tipagem forte em todos os métodos
- Organização: código mais limpo e organizado
- Performance: cache reduz verificações desnecessárias
- Observabilidade: métricas detalhadas para monitoramento

## [2.3.0] - 2025-08-05

### Alterado

- Atualização de dependências: atualizado `jonjon1992/s3-php` de v2.1.0 para v2.4.0
- Melhorias de compatibilidade: suporte a novas funcionalidades do S3 PHP
- Otimizações de performance: melhorias gerais no framework

## [2.2.9] - 2025-08-05

### Adicionado

- Melhorias na documentação: atualização completa da documentação do projeto
- Organização de código: reestruturação e limpeza do código base
- Preparação para testes: estrutura preparada para implementação de testes unitários

### Alterado

- Versão atualizada: framework atualizado para versão 2.2.9
- Documentação aprimorada: README.md e CHANGELOG.md atualizados

## [2.2.8] - 2025-08-05

### Adicionado

- Versão estável: framework na versão 2.2.8 com todas as funcionalidades principais
- Integração completa AWS: S3, SQS e DynamoDB totalmente integrados
- Sistema de helpers: helpers para todos os serviços principais
- Middleware completo: sistema de middlewares para autenticação, CORS, rate limiting, etc.

### Alterado

- Framework maduro: todas as funcionalidades principais implementadas e testadas
- Documentação completa: README.md e CHANGELOG.md atualizados
- Estrutura modular: sistema de módulos funcionando perfeitamente

## [2.2.7] - 2025-08-05

### Adicionado

- Novo helper DynamoDB: implementado helper `dynamo()` em `src/helpers.php` seguindo o padrão dos outros helpers
- Integração com sistema de serviços: adicionado suporte para configurações do DynamoDB em `src/services.php`
- Suporte a múltiplas configurações: helper suporta alias para diferentes ambientes (local, produção, etc.)
- Documentação específica: criado `docs/utils/dynamo-helper.md` com documentação completa do helper
- Exemplo de uso: atualizado `examples/dynamo-example.php` com exemplos do helper
- Testes unitários: criado `tests/Utils/DynamoHelperTest.php` para validar o helper

### Alterado

- Padrão consistente: helper `dynamo()` segue o mesmo padrão dos outros helpers (redis, s3, db, etc.)
- Integração com DI: helper integra automaticamente com o sistema de injeção de dependência
- Configuração flexível: suporte a variáveis de ambiente e configurações customizadas

### Corrigido

- Consistência de helpers: todos os helpers agora seguem o mesmo padrão de implementação

## [2.2.6] - 2025-08-05

### Corrigido

- Conflito de função s3(): adicionado `if (!function_exists('s3'))` em `src/helpers.php` para evitar redeclaração da função
- Assinaturas incompatíveis: corrigidas assinaturas incompatíveis na classe `Str` removendo type hints `string` dos métodos `endsWith()`, `camel()`, `ucfirst()` e `studly()`
- Compatibilidade com pacotes: resolvidos conflitos entre `jonjon1992/php-slim-modular`, `jonjon1992/s3-php` e `illuminate/support` v10.48.28

### Alterado

- Compatibilidade: melhorada compatibilidade com dependências externas

## [2.2.5] - 2025-08-05

### Adicionado

- Novo util DynamoDB: implementado `Core\Utils\Dynamo` para integração com Amazon DynamoDB
- Suporte completo CRUD: suporte completo para operações CRUD no DynamoDB
- Configuração automática: configuração automática para ambiente local (LocalStack) e produção (AWS)
- Operações em lote: operações em lote (batch operations)
- Queries e scans: suporte a queries e scans
- Gerenciamento de tabelas: gerenciamento de tabelas (criar, listar, descrever, remover)
- Conversão flexível: conversão flexível de dados (objetos, arrays, stdClass)
- Documentação completa: documentação completa em `docs/utils/dynamo.md`
- Exemplo de uso: exemplo de uso em `examples/dynamo-example.php`
- Integração: integração com a package `jonjon1992/php-dynamo` v3.0.2

### Alterado

- README atualizado: atualizado README.md com seção de uso do DynamoDB
- Documentação: melhorada documentação de utils do framework

## [2.2.4] - 2025-08-05

### Corrigido

- HttpException: corrigido uso de `HttpException` para `HttpInternalServerErrorException` no `Action.php`
- Classes de exceção: criadas classes de exceção personalizadas para melhor tratamento de erros:
  - `AuthenticationException` para erros de autenticação
  - `InvalidRequestException` para erros de validação de requisição
  - `CommandException` para erros de execução de comandos
  - `AppException` para erros gerais da aplicação
- Exceções específicas: atualizado uso de `Exception` genérica para exceções específicas em todo o framework

### Alterado

- Segurança de tipos: melhorada segurança de tipos e tratamento de erros

## [2.2.3] - 2025-08-05

### Corrigido

- Conflito de dependências: resolvido conflito adicional de dependências com `slim/psr7` e `slim/slim`
- Atualização slim/psr7: atualizado `slim/psr7` de `1.6.1` para `^1.7` para suportar `psr/http-message ^2.0`
- Atualização slim/slim: atualizado `slim/slim` de `4.8.1` para `^4.9` para melhor compatibilidade
- Conflitos PSR: corrigidos todos os conflitos de dependências PSR HTTP Message

## [2.2.2] - 2025-08-05

### Corrigido

- Conflito AWS SDK: resolvido conflito de dependências entre `aws/aws-sdk-php` e `slim/php-view`
- Atualização slim/php-view: atualizado `slim/php-view` de `3.1.0` para `^3.2` para suportar `psr/http-message ^2.0`
- Compatibilidade AWS: corrigido problema de compatibilidade com AWS SDK PHP ^3.350

## [2.2.1] - 2025-08-05

### Alterado

- Atualização S3 PHP: atualizado `jonjon1992/s3-php` de `v2.0.0` para `v2.1.0`

## [2.2.0] - 2025-08-05

### Adicionado

- AWS SDK PHP: AWS SDK PHP atualizado para versão ^3.350
- Helper S3: função helper `s3()` para acessar buckets S3
- Helper Queue: função helper `queue()` para acessar clientes SQS
- Métodos String: métodos `endsWith()`, `camel()`, `ucfirst()` e `studly()` na classe `Str`

### Corrigido

- Exception sem namespace: corrigido uso de `Exception` sem namespace em múltiplos arquivos
- Propriedade não utilizada: removida propriedade `$client` não utilizada no `SqsClient`
- Variável não definida: corrigida variável `$service` não definida no `ImAliveAction`
- Import da classe Str: adicionado import da classe `Str` no `Trace.php`
- Validação de endpoint: melhorada validação do endpoint no `SqsClient` para evitar erros quando não fornecido
- Verificação mime_content_type: adicionada verificação se `mime_content_type()` existe antes de usá-la no `S3.php`
- Exception sem namespace: corrigidos todos os usos de `Exception` sem namespace nos seguintes arquivos:
  - `src/helpers.php`
  - `src/Action/ImAliveAction.php`
  - `src/Commands/MigrateCreateCommand.php`
  - `src/Commands/MigrateRunCommand.php`
  - `src/Utils/Crypt.php`

### Alterado

- AWS SDK PHP: AWS SDK PHP atualizado de `3.324.0` para `^3.350`
- Configuração SqsClient: melhorada configuração do SqsClient para ser mais robusta
- Verificações de segurança: adicionadas verificações de segurança para endpoints opcionais

### Segurança

- Validação de entrada: melhorada validação de entrada de dados
- Verificações de segurança: adicionadas verificações de segurança para configurações AWS

## [2.1.0] - 2025-08-05

### Adicionado

- Funcionalidades iniciais: funcionalidades iniciais do framework
- Sistema de módulos: suporte a módulos
- Sistema de migrações: sistema de migrações
- Integração AWS: integração com AWS S3 e SQS
- Sistema de autenticação: sistema de autenticação e criptografia
- Middlewares personalizados: middlewares personalizados

## [2.9.8] - 2025-11-19

### Adicionado

- atualiza documentação de contexto
