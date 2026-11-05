# CHANGELOG

### v3.2.0
 - CacheMiddleware: tudo fica no DB 0 do Redis (a chave já separa por usuário); acabou o `SELECT` por requisição, que custava idas extras ao Redis e não funciona em Redis Cluster. O cache antigo nos DBs 1–15 expira sozinho (até 24h)
 - Redis: opção `persistent` na config do cache para reaproveitar a conexão (TCP/TLS) entre requisições, com pool separado por credencial
 - Banco: um único gerenciador do Eloquent com todas as conexões; models sem `$connection` usam o banco do módulo da requisição (ou `db.default`) e podem declarar `$connection = '<alias>'`
 - Migrations: só usam transação em bancos que revertem DDL (PostgreSQL, SQLite, SQL Server); no MySQL/MariaDB a transação não protegia nada e podia falhar com "There is no active transaction"
 - JWT: tokens sem `exp` são rejeitados (`JWT_REQUIRE_EXP=false` desativa); `JWT_SECRET` ausente não gera mais TypeError
 - CORS: um único `Cache-Control` (o segundo sobrescrevia o `no-store`); removido `Access-Control-Allow-Credentials: *` (valor inválido); `Access-Control-Expose-Headers` passa a listar os headers do framework (rate limit, cache)
 - ExceptionHandler: o mapa apontava para uma classe inexistente (`Core\Exceptions\AppException`); `AppException` sem código passa a responder 400. Os erros de inicialização usam 500 explicitamente
 - Bootstrap: `.env` opcional (`safeLoad`), útil quando as variáveis vêm do ambiente
 - PHP 8.4: parâmetros nullable explícitos em `ErrorHandler`, `DomainException`, `InfraException` e `ValidatorException`; `declare(strict_types=1)` em 16 arquivos revisados
 - Dependências: `guzzlehttp/guzzle` e `respect/validation` declaradas (eram usadas sem estar no `composer.json`); bibliotecas de terceiros com `^` dentro da mesma versão principal; `phpstan/phpstan` ^1.12, `phpstan.neon.dist` (nível 5) e script `composer analyse`
 - **Atenção ao atualizar:** tokens JWT sem `exp` deixam de ser aceitos; models que dependiam do último banco resolvido devem declarar `$connection`

### v3.1.0
 - Respostas JSON: compactas em production/staging (formatadas nos demais ambientes), sem escapar acentos e barras (`JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES`); o conteúdo é o mesmo, só muda a formatação
 - AbstractAction: as listas `columns`, `not-columns` e `columns-decoded` são lidas uma vez por requisição (antes eram relidas para cada chave do payload); a conversão intermediária não usa mais pretty print
 - Serializable: a reflexão dos getters é feita uma vez por classe; getters públicos são chamados diretamente
 - Container: em production/staging, com APCu, as definições de autowiring do PHP-DI ficam em cache (`APP_CONTAINER_CACHE=false` desativa)
 - Bootstrap: cada módulo é instanciado uma única vez (antes também era criado de novo para registrar as views)
 - CacheMiddleware: o corpo da resposta é lido direto do stream; `respond()` fica obsoleto (enviava headers com `header()`)
 - Correções: `SnsClient::topic()` com condição invertida; `AbstractFilters::hydrate()` sem `page`/`per_page` gerava TypeError e os getters falhavam com propriedades não inicializadas; `Crypt::response()` com `Paginator` gerava TypeError e `Crypt::transform()` quebrava com valores escalares; `/version` respondia 500 quando o `composer.json` não tinha `version` (agora responde 204)
 - Dynamo: as exceções do SDK são propagadas com o tipo original (antes viravam `Exception` genérica com a mensagem prefixada)

### v3.0.5
 - Segurança: remove `auth.json` (token do GitHub) do versionamento e adiciona `.gitattributes` com `export-ignore`
 - ImAliveAction: o cache do health check expira em 30s (antes nunca expirava); uma falha invalida o cache; `?service=` não usa nem alimenta o cache; o campo `error` é omitido em production/staging; qualquer `Throwable` marca o serviço como `dead`
 - RateLimitMiddleware: corrige a chamada a `withHeaders()`, que não existe no PSR-7; contador atômico com `INCR`; `X-Forwarded-For` só é usado com `trustProxyHeaders: true` (necessário atrás de ALB/API Gateway/Nginx); modo `user` usa `auth.user_id`; modo `token` grava um hash do token
 - CacheMiddleware: `destroy()` volta a invalidar o cache (a verificação de `CACHE_MIDDLEWARE` aceita bool ou string); expirações vindas do `.env` são convertidas para `int`
 - Redis: `inDatabase()` sempre volta ao DB 0, mesmo com erro; `calcDbIndex()` aceita IDs não numéricos (UUID)
 - QueueWorkCommand: remove a opção `--verbose` duplicada, que conflitava com a opção global do Symfony Console (use `-v`)
 - App: `module()` não quebra mais quando `REQUEST_URI` não existe (CLI/testes)
 - BaseTestCase: corrige a ordem dos argumentos em get/post/put/delete; `configDbSqlite` processava os bancos que NÃO eram SQLite (podendo rodar `chown -R` no diretório atual); os mocks de Redis/Dynamo passam a ser registrados; `cleanTestData` passa a funcionar
 - Obsoletos, com remoção prevista na 4.0: `ClientMiddleware`, `Client`, `IClient`, `client()` e `CreateClientCommand` (o `x-client` não é repassado pelo ApiGatewayMiddleware desde a v2.12.5)

### v3.0.4
 - CacheMiddleware: ajuste na verificação de `CACHE_MIDDLEWARE` em `destroy()`

### v3.0.3
 - CacheMiddleware: corrige invalidação por padrão no Redis removendo uso de reflexão para `hDel` e executando remoção variádica diretamente no wrapper

### v3.0.2
 - Redis: adiciona suporte a autenticação ACL com `username` + `password` (`auth([$username, $password])`)
 - Redis: mantém fallback para autenticação legada apenas com senha

### v3.0.1
 - ApiGatewayMiddleware: simplifica fluxo removendo validação por `x-client`/`xclient` e lookup de módulo em cache/banco
 - CacheMiddleware: corrige imports para `Core\Utils\Redis` e `Core\Utils\Time`
 - Limpeza de código em ExceptionHandler e CorsMiddleware

### v3.0.0
 - PHP: eleva o requisito mínimo para PHP 8.4
 - Dependências: atualiza `laminas/laminas-hydrator` para `4.18.0`
 - Dependências: atualiza `nette/mail` para `v4.0.4`
 - Mailer: adapta `MailerServiceTrait` para a API do `SmtpMailer` do `nette/mail` 4

### v2.12.6
 - Redis: adiciona suporte a TLS/SSL via `scheme` e contexto `stream` (incluindo `ssl_ca`)
 - Docs: adiciona exemplo de configuração Redis TCP/TLS no README

### v2.12.5
 - Ajusta na abstract action no encode

v2.12.4
Adiciona debug no sql em dev

v2.12.3
Adiciona dependência lcobucci/clock para JwtValidator (fix SystemClock not found)

v2.12.2
Implementa IJwtValidator com lcobucci/jwt (HS256, LooseValidAt)
Substitui UUID por ID sequencial em trace-id (Str::sequentialId)
Remove dependência ramsey/uuid

v2.12.1
Altera formato do CHANGELOG para padrão simplificado (versão + linhas)

v2.12.0
Adiciona IAuth, IJwtValidator, ApiGatewayMiddleware e Auth
Remove JwtAuthMiddleware (substituído por ApiGatewayMiddleware)
Alterações em cli.php, QueueWorkCommand, BaseCollection e SnsClient

v2.11.35
Remove arquivos e documentação obsoletos
Ajustes em .gitignore e QueueWorkCommand

v2.11.32
StorageServiceTrait: evita erro "Storage: Alias cannot be empty"
Adiciona workflow GitHub Actions para criar tag e release a partir do composer

v2.11.30
QueueServiceTrait: prevenção de erro 'Alias cannot be empty'

v2.11.29
Configuração SQS: atualizados padrões (região sa-east-1, URLs completas)

v2.11.28
QueueServiceTrait: adicionado suporte a SQS Connector

v2.11.27
QueueWorkCommand: suporte a conexão 'default' em módulos

v2.11.26
QueueWorkCommand: aprimorada lógica de fallback de conexão

v2.11.25
QueueWorkCommand: corrigida seleção padrão de conexão

v2.11.24
QueueServiceTrait: corrigido retorno do RedisConnector para usar wrapper Redis

v2.11.23
Redis Util: implementado adaptador no método eval() para PhpRedis

v2.11.22
Redis Util: assinatura do método eval() totalmente removida

v2.11.21
Redis Util: uso de func_get_args() no método eval()

v2.11.20
Redis Util: relaxada assinatura do método eval()

v2.11.19
Redis Util: adicionado método público eval()

v2.11.18
Redis Util: adicionado método público client()

v2.11.17
QueueServiceTrait: adicionado suporte a Redis Queue

v2.11.16
QueueWorkCommand: definido contexto do módulo manualmente

v2.11.15
App: corrigido método module() em ambiente CLI

v2.11.14
QueueServiceTrait: adicionado binding da interface Container

v2.11.13
QueueServiceTrait: adicionado alias bus para Bus\Dispatcher

v2.11.12
QueueServiceTrait: adicionado binding do Bus\Dispatcher

v2.11.11
QueueWorkCommand: tratamento de exceção para nomes de job inválidos

v2.11.10
QueueWorkCommand: resolvido erro "Array to string conversion" em logs do worker

v2.11.9
Module: adicionado método queue() na interface IModule e classe Module
Imports atualizados para Core\Utils

v2.11.8
QueueServiceTrait: corrigido dbResolver para usar service()

v2.11.6
QueueServiceTrait: ajustada ordem de inicialização

v2.11.5
QueueServiceTrait: registrando connectors manualmente

v2.11.4
QueueServiceTrait: simplificado seguindo padrão do DatabaseServiceTrait

v2.11.3
QueueServiceTrait: refatorado com singleton bindings

v2.11.2
QueueServiceTrait: removido setDefaultConnection() que não existe em SyncQueue

v2.11.1
QueueServiceTrait: adicionado alias default para compatibilidade com Laravel Queue Worker

v2.11.0
Refatora estrutura de Queue: um QueueManager por módulo com todas as conexões
Helper queue() simplificado para aceitar apenas módulo

v2.10.5
QueueWorkCommand: corrigido WorkerOptions para usar connectionName dinâmico

v2.10.4
QueueServiceTrait: adicionado setDefaultConnection() ao QueueManager

v2.10.3
QueueServiceTrait: corrigido retorno do service binding para QueueManager

v2.10.2
QueueServiceTrait: corrigida implementação do binding db para ConnectionResolverInterface

v2.10.1
QueueWorkCommand: corrigido para usar estrutura modular queue.{module}.{connection}
Adicionada opção --module no comando queue:work

v2.10.0
Service Traits: criadas 8 traits modulares (Log, Database, Cache, Storage, Queue, Dynamo, View, Mailer)
Queue Modular: suporte a múltiplos módulos com estrutura aninhada

v2.9.18
Corrigidos namespaces e imports (Core\Traits para Core\Utils)

v2.9.17
Migração de Traits para Utils: Env, Path e Trace

v2.9.16
Clients AWS: extraída lógica comum para trait AwsClientBase

v2.9.15
Melhorias na tipagem e limpeza de código

v2.9.14
ExceptionHandler: corrigido erro de tipo em getHttpStatusCode()

v2.9.13
CreateClientCommand: corrigido nome da tabela para module_client_keys

v2.9.12
Relaxada restrição do phpunit para ^10.5 (correção de segurança)
Removidos campos homepage e support do composer.json

v2.9.11
Atualizado aws/aws-sdk-php para 3.368.0.0

v2.9.10
Bootstrap: corrigido carregamento para cors.php ao invés de config.php

v2.9.9
Redis getOrSet: verificação estrita e melhor tratamento de expiração

v2.9.8
Atualiza documentação de contexto

v2.9.7
Redis: método connectInternal() e correção de autenticação
Substituído rand() por random_int(), == por ===

v2.9.6
ValueObjects: adicionado método getValue()
Str::code(): corrigido parâmetro faltante

v2.9.5
Melhorias no sistema de testes e comandos de migração

v2.9.4
Melhorias no BaseTestCase com tipagem e organização

v2.9.3
Implementação do DynamoMock

v2.9.2
BaseTestCase: assertJsonResponse, createTestData, cleanTestData, RedisMock
Segurança com escapeshellarg()

v2.9.1
Crypt: corrigido erro de tipo no encode() com Hashids
Conflito de funções dd() e d() com VarDumper

v2.9.0
Sistema de exceções: melhorada ordem de carregamento no bootstrap

v2.8.9
Funções de debug dd() e d()

v2.8.8
Conflito env() com Laravel: renomeada para getEnvVar()

v2.8.7
Conflito env() com Laravel: renomeada para getEnvVar()

v2.8.6
Sistema de exceções: corrigido carregamento no bootstrap
ErrorHandler: corrigida propriedade $response

v2.8.5
Proteção contra TypeError em str_ends_with

v2.8.4
TypeError em str_ends_with com chaves numéricas

v2.8.3
Bootstrap: removido autoloader

v2.8.2
Conflito de tipo na propriedade response do ImAliveAction

v2.8.1
Problemas com substr e valores null no AbstractAction

v2.8.0
Scripts de automação para commits e releases

v2.7.0
Atualização de datas nos documentos

v2.6.0
Sistema de validação com Respect\Validation
Scripts de automação (release, quick-commit, setup-aliases)

v2.5.0
Tipagem forte declare(strict_types=1) em todo o projeto
Compatibilidade PHP 8.2+

v2.4.0
Health check avançado no ImAliveAction
Suporte DynamoDB, métricas detalhadas, cache inteligente

v2.3.0
Atualizado jonjon1992/s3-php para v2.4.0

v2.2.9
Melhorias na documentação e organização do código

v2.2.8
Versão estável com integração AWS completa
Sistema de helpers e middlewares

v2.2.7
Helper dynamo() para DynamoDB

v2.2.6
Conflito de função s3() e assinaturas na classe Str
Compatibilidade com illuminate/support

v2.2.5
Util DynamoDB: Core\Utils\Dynamo com CRUD completo

v2.2.4
Classes de exceção personalizadas (AuthenticationException, InvalidRequestException, etc.)

v2.2.3
Conflito de dependências slim/psr7 e slim/slim

v2.2.2
Conflito AWS SDK com slim/php-view

v2.2.1
Atualizado jonjon1992/s3-php para v2.1.0

v2.2.0
AWS SDK PHP ^3.350, helpers s3() e queue()
Métodos Str: endsWith, camel, ucfirst, studly

v2.1.0
Funcionalidades iniciais: módulos, migrações, AWS S3/SQS, autenticação, middlewares
