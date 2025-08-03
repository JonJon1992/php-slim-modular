<?php

declare(strict_types=1);

namespace Core\Abstract;

use Core\Contracts\ServiceProvider\IServiceProvider;
use DI\ContainerBuilder;

/**
 * Classe abstrata base para Service Providers
 */
abstract class AbstractServiceProvider implements IServiceProvider
{
    protected ContainerBuilder $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * Registrar serviços no container
     */
    abstract public function register(ContainerBuilder $container): void;

    /**
     * Boot dos serviços (executado após registro)
     */
    public function boot(): void
    {
        // Implementação padrão vazia
        // Pode ser sobrescrita pelos providers filhos
    }

    /**
     * Verificar se o provider deve ser carregado
     */
    public function shouldLoad(): bool
    {
        return true;
    }

    /**
     * Registrar singleton no container
     */
    protected function singleton(string $abstract, string $concrete): void
    {
        $this->container->addDefinitions([
            $abstract => \DI\create($concrete)
        ]);
    }

    /**
     * Registrar binding no container
     */
    protected function bind(string $abstract, string $concrete): void
    {
        $this->container->addDefinitions([
            $abstract => \DI\create($concrete)
        ]);
    }

    /**
     * Registrar instância no container
     */
    protected function instance(string $abstract, $instance): void
    {
        $this->container->addDefinitions([
            $abstract => $instance
        ]);
    }

    /**
     * Registrar factory no container
     */
    protected function factory(string $abstract, callable $factory): void
    {
        $this->container->addDefinitions([
            $abstract => \DI\factory($factory)
        ]);
    }

    /**
     * Registrar alias no container
     */
    protected function alias(string $abstract, string $alias): void
    {
        $this->container->addDefinitions([
            $alias => \DI\get($abstract)
        ]);
    }
} 