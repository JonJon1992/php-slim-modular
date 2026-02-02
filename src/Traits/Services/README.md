# Service Traits

Esta pasta contém traits modulares para registro de serviços no container DI.

## 📁 Estrutura

```
src/Traits/Services/
├── LogServiceTrait.php         # Registro de serviços de log (Monolog)
├── DatabaseServiceTrait.php    # Registro de conexões de banco de dados (Eloquent)
├── CacheServiceTrait.php       # Registro de serviços de cache (Redis)
├── StorageServiceTrait.php     # Registro de serviços de storage (S3)
├── QueueServiceTrait.php       # Registro de serviços de queue (Laravel Queue)
├── DynamoServiceTrait.php      # Registro de serviços de DynamoDB
├── ViewServiceTrait.php        # Registro de serviços de view/render
└── MailerServiceTrait.php      # Registro de serviços de email (SMTP)
```

## 🎯 Uso

Cada trait é usado no arquivo `src/services.php` e é responsável por:

1. Ler as configurações do respectivo serviço
2. Criar os bindings no container DI
3. Registrar as instâncias dos serviços

## 📝 Exemplo

```php
// src/services.php
use Core\Traits\Services\LogServiceTrait;

return new class {
    use LogServiceTrait;

    public function __invoke(ContainerBuilder $builder): void
    {
        $configs = Config::singleton();
        $bindings = [];

        $this->registerLogServices($bindings, $configs);

        $builder->addDefinitions($bindings);
    }
};
```

## ✅ Benefícios

- **Modularidade**: Cada trait é responsável por um tipo de serviço
- **Reutilização**: Traits podem ser usadas em diferentes contextos
- **Manutenção**: Mais fácil de manter e testar
- **Organização**: Código mais limpo e organizado
- **Escalabilidade**: Fácil adicionar novos tipos de serviços

## 🔧 Como adicionar um novo serviço

1. Crie uma nova trait em `src/Traits/Services/NomeServiceTrait.php`
2. Implemente o método `registerNomeServices(array &$bindings, $configs): void`
3. Use a trait no `src/services.php`
4. Chame o método de registro no `__invoke()`

```php
<?php

namespace Core\Traits\Services;

trait NovoServiceTrait
{
    protected function registerNovoServices(array &$bindings, $configs): void
    {
        foreach ($configs->get('novo', []) as $alias => $settings) {
            if ($alias === 'default') {
                continue;
            }
            $bindings["novo.$alias"] = function () use ($settings) {
                return new NovoService($settings);
            };
        }
    }
}
```
