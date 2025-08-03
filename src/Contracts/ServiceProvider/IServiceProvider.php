<?php

declare(strict_types=1);

namespace Core\Contracts\ServiceProvider;

use DI\ContainerBuilder;

/**
 * Interface para Service Providers
 */
interface IServiceProvider
{
    /**
     * Registrar serviços no container
     */
    public function register(ContainerBuilder $container): void;

    /**
     * Boot dos serviços (executado após registro)
     */
    public function boot(): void;

    /**
     * Verificar se o provider deve ser carregado
     */
    public function shouldLoad(): bool;
} 