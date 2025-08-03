<?php

declare(strict_types=1);

namespace Core\Abstract;

use Core\Contracts\Service\IService;
use Core\Utils\Trace;
use Psr\Log\LoggerInterface;

/**
 * Classe abstrata base para services
 */
abstract class AbstractService implements IService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log de informação
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, array_merge($context, [
            'service' => static::class,
            'trace_id' => Trace::getId()
        ]));
    }

    /**
     * Log de erro
     */
    protected function logError(string $message, array $context = []): void
    {
        $this->logger->error($message, array_merge($context, [
            'service' => static::class,
            'trace_id' => Trace::getId()
        ]));
    }

    /**
     * Log de warning
     */
    protected function logWarning(string $message, array $context = []): void
    {
        $this->logger->warning($message, array_merge($context, [
            'service' => static::class,
            'trace_id' => Trace::getId()
        ]));
    }

    /**
     * Log de debug
     */
    protected function logDebug(string $message, array $context = []): void
    {
        $this->logger->debug($message, array_merge($context, [
            'service' => static::class,
            'trace_id' => Trace::getId()
        ]));
    }
} 