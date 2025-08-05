# Migração para PHP-DI 7.0 e Atributos

## Visão Geral

Este documento descreve a migração do projeto para PHP-DI 7.0 e o uso dos novos atributos nativos do PHP 8.0+ para injeção de dependência.

## Mudanças Implementadas

### 1. Atualização de Dependências

- **PHP**: Atualizado para 8.2.29
- **PHP-DI**: Atualizado de 6.3.4 para 7.0.11
- **Extensões PHP**: Instaladas todas as extensões necessárias (intl, redis, etc.)

### 2. Configuração do Container

#### Antes (PHP-DI 6.x)
```php
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);

// Verificação de compatibilidade
if (method_exists($containerBuilder, 'useAttributes')) {
    $containerBuilder->useAttributes(true);
}
```

#### Depois (PHP-DI 7.0+)
```php
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->useAttributes(true); // Sempre disponível
```

### 3. Uso de Atributos

#### Atributo #[Injectable]
```php
use DI\Attribute\Injectable;

#[Injectable(lazy: true)]
class UserService
{
    // Classe marcada como injetável com lazy loading
}
```

#### Atributo #[Inject] em Propriedades
```php
use DI\Attribute\Inject;

class UserController
{
    #[Inject]
    private UserService $userService;
    
    #[Inject('app.name')]
    private string $appName;
}
```

#### Atributo #[Inject] no Construtor
```php
class EmailService
{
    public function __construct(
        #[Inject('mailer.default')] private $mailer,
        #[Inject('app.name')] private string $appName
    ) {}
}
```

#### Atributo #[Inject] em Métodos
```php
#[Inject]
public function setLogger(#[Inject('log.default')] $logger): void
{
    // Método chamado automaticamente com dependência injetada
}
```

## Vantagens dos Atributos

### 1. Performance
- **Atributos nativos**: Mais rápidos que annotations
- **Compilação**: Melhor suporte a cache e compilação
- **Reflection**: Menos overhead de reflection

### 2. Tipagem
- **Type safety**: Melhor verificação de tipos
- **IDE support**: Autocompletar e navegação melhorados
- **Static analysis**: Suporte a ferramentas de análise estática

### 3. Sintaxe
- **PHP 8.0+**: Usa recursos nativos da linguagem
- **Legibilidade**: Código mais limpo e expressivo
- **Consistência**: Padrão moderno do PHP

## Exemplos Práticos

### Exemplo 1: Service com Múltiplas Dependências
```php
#[Injectable(lazy: true)]
class UserService
{
    #[Inject]
    private Config $config;
    
    #[Inject]
    private Redis $redis;
    
    #[Inject]
    private Dynamo $dynamo;
    
    public function getUser(int $id): array
    {
        // Lógica usando dependências injetadas
    }
}
```

### Exemplo 2: Controller com Injeção de Configuração
```php
class UserController
{
    #[Inject]
    private UserService $userService;
    
    #[Inject('app.name')]
    private string $appName;
    
    public function show(int $id): array
    {
        return [
            'app' => $this->appName,
            'user' => $this->userService->getUser($id)
        ];
    }
}
```

### Exemplo 3: Injeção de Múltiplos Parâmetros
```php
#[Inject(['db.host', 'db.port'])]
public function configureDatabase(string $host, int $port): void
{
    // Configuração com múltiplos parâmetros
}
```

## Configuração do Bootstrap

### Arquivo: `src/bootstrap.php`
```php
// Carrega o autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Carrega helpers globais
require_once __DIR__ . '/helpers.php';

// Configuração do container
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->useAttributes(true); // PHP-DI 7.0+
```

## Migração de Annotations

### Antes (Annotations)
```php
/**
 * @Inject
 * @var UserService
 */
private $userService;

/**
 * @Inject
 * @param UserService $service
 */
public function setService($service)
{
}
```

### Depois (Atributos)
```php
use DI\Attribute\Inject;

#[Inject]
private UserService $userService;

#[Inject]
public function setService(UserService $service): void
{
}
```

## Requisitos do Sistema

### PHP
- **Versão**: 8.2.0 ou superior
- **Extensões**: intl, redis, curl, zip, pdo, mbstring, xml

### Composer
- **PHP-DI**: ^7.0
- **Autoloader**: PSR-4

## Testes

### Verificar Instalação
```bash
php --version  # Deve mostrar 8.2+
composer show php-di/php-di  # Deve mostrar 7.0+
```

### Testar Atributos
```bash
php examples/php-di-attributes-example.php
```

## Benefícios para o Projeto

1. **Modernização**: Usa recursos modernos do PHP 8.2+
2. **Performance**: Melhor performance de injeção de dependência
3. **Manutenibilidade**: Código mais limpo e tipado
4. **Compatibilidade**: Melhor integração com IDEs e ferramentas
5. **Futuro**: Preparado para versões futuras do PHP

## Próximos Passos

1. **Migrar classes existentes**: Converter annotations para atributos
2. **Testes**: Implementar testes para classes com atributos
3. **Documentação**: Atualizar documentação de desenvolvimento
4. **CI/CD**: Atualizar pipelines para PHP 8.2+

## Referências

- [PHP-DI 7.0 Documentation](https://php-di.org/doc/attributes.html)
- [PHP 8.0 Attributes](https://www.php.net/manual/en/language.attributes.php)
- [Context7 PHP-DI Documentation](https://context7.com/php-di-doc)

---

**Versão**: 2.4.0  
**Data**: 05/08/2025  
**Autor**: PHP Slim Modular Framework 