<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Utils\Redis;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * Middleware de Rate Limiting
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    private Redis $redis;
    private int $maxRequests;
    private int $windowSeconds;
    private string $identifier;

    public function __construct(
        Redis $redis,
        int $maxRequests = 100,
        int $windowSeconds = 60,
        string $identifier = 'ip'
    ) {
        $this->redis = $redis;
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
        $this->identifier = $identifier;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $key = $this->getRateLimitKey($request);
        
        $current = $this->redis->get($key) ?: 0;
        
        if ($current >= $this->maxRequests) {
            return $this->createRateLimitResponse();
        }

        $this->redis->incr($key);
        $this->redis->expire($key, $this->windowSeconds);

        $response = $handler->handle($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => (string) $this->maxRequests,
            'X-RateLimit-Remaining' => (string) max(0, $this->maxRequests - $current - 1),
            'X-RateLimit-Reset' => (string) (time() + $this->windowSeconds)
        ]);
    }

    /**
     * Obter chave para rate limiting
     */
    private function getRateLimitKey(ServerRequestInterface $request): string
    {
        $identifier = match ($this->identifier) {
            'ip' => $this->getClientIp($request),
            'user' => $this->getUserId($request),
            'token' => $this->getToken($request),
            default => $this->getClientIp($request)
        };

        $window = floor(time() / $this->windowSeconds);
        
        return "rate_limit:{$identifier}:{$window}";
    }

    /**
     * Obter IP do cliente
     */
    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        
        return $serverParams['HTTP_X_FORWARDED_FOR'] 
            ?? $serverParams['HTTP_X_REAL_IP'] 
            ?? $serverParams['REMOTE_ADDR'] 
            ?? 'unknown';
    }

    /**
     * Obter ID do usuário
     */
    private function getUserId(ServerRequestInterface $request): string
    {
        // Implementar lógica para obter ID do usuário autenticado
        return $request->getAttribute('user_id', 'anonymous');
    }

    /**
     * Obter token de autenticação
     */
    private function getToken(ServerRequestInterface $request): string
    {
        return $request->getHeaderLine('Authorization') ?: 'anonymous';
    }

    /**
     * Criar resposta de rate limit excedido
     */
    private function createRateLimitResponse(): ResponseInterface
    {
        $response = new Response();
        
        $response->getBody()->write(json_encode([
            'error' => 'Rate limit exceeded',
            'message' => 'Too many requests. Please try again later.',
            'retry_after' => $this->windowSeconds
        ]));

        return $response
            ->withStatus(429)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Retry-After', (string) $this->windowSeconds);
    }
} 