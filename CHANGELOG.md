# CHANGELOG

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
