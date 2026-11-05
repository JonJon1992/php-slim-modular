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
 * Middleware de Rate Limiting (janela fixa).
 *
 * Por padrão o cliente é identificado por REMOTE_ADDR. Atrás de um proxy/load balancer
 * (ALB, API Gateway, Nginx), passe $trustProxyHeaders = true para usar o X-Forwarded-For;
 * sem um proxy confiável, esse header pode ser forjado pelo cliente para burlar o limite.
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    private Redis $redis;
    private int $maxRequests;
    private int $windowSeconds;
    private string $identifier;
    private bool $trustProxyHeaders;

    public function __construct(
        Redis $redis,
        int $maxRequests = 100,
        int $windowSeconds = 60,
        string $identifier = 'ip',
        bool $trustProxyHeaders = false
    ) {
        $this->redis = $redis;
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = max(1, $windowSeconds);
        $this->identifier = $identifier;
        $this->trustProxyHeaders = $trustProxyHeaders;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $window = intdiv(time(), $this->windowSeconds);
        $resetAt = ($window + 1) * $this->windowSeconds;
        $key = "rate_limit:{$this->getIdentifier($request)}:{$window}";

        // INCR é atômico: evita a corrida entre ler e incrementar o contador.
        $current = (int) $this->redis->incr($key);
        if ($current === 1) {
            $this->redis->expire($key, $this->windowSeconds);
        }

        if ($current > $this->maxRequests) {
            return $this->createRateLimitResponse($resetAt);
        }

        $response = $handler->handle($request);

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $this->maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string) max(0, $this->maxRequests - $current))
            ->withHeader('X-RateLimit-Reset', (string) $resetAt);
    }

    private function getIdentifier(ServerRequestInterface $request): string
    {
        return match ($this->identifier) {
            'user' => $this->getUserId($request),
            'token' => $this->getToken($request),
            default => 'ip:' . $this->getClientIp($request),
        };
    }

    private function getClientIp(ServerRequestInterface $request): string
    {
        if ($this->trustProxyHeaders) {
            // O proxy confiável acrescenta o IP de quem o chamou ao FINAL da lista;
            // os valores à esquerda vêm do cliente e podem ser forjados.
            $forwarded = array_filter(array_map('trim', explode(',', $request->getHeaderLine('X-Forwarded-For'))));
            if (!empty($forwarded)) {
                return (string) end($forwarded);
            }

            $realIp = trim($request->getHeaderLine('X-Real-IP'));
            if ($realIp !== '') {
                return $realIp;
            }
        }

        return (string) ($request->getServerParams()['REMOTE_ADDR'] ?? 'unknown');
    }

    /**
     * Usa o ID definido pelo ApiGatewayMiddleware (auth.user_id); requisições anônimas
     * caem no limite por IP em vez de compartilharem um único contador.
     */
    private function getUserId(ServerRequestInterface $request): string
    {
        $userId = $request->getAttribute('auth.user_id') ?? $request->getAttribute('user_id');

        if ($userId === null || $userId === '') {
            return 'ip:' . $this->getClientIp($request);
        }

        return 'user:' . $userId;
    }

    /**
     * Usa um hash do token para não gravar credenciais nas chaves do Redis.
     */
    private function getToken(ServerRequestInterface $request): string
    {
        $token = $request->getHeaderLine('Authorization');

        if ($token === '') {
            return 'ip:' . $this->getClientIp($request);
        }

        return 'token:' . hash('sha256', $token);
    }

    private function createRateLimitResponse(int $resetAt): ResponseInterface
    {
        $retryAfter = max(1, $resetAt - time());
        $response = new Response();

        $response->getBody()->write(json_encode([
            'error' => 'Rate limit exceeded',
            'message' => 'Too many requests. Please try again later.',
            'retry_after' => $retryAfter
        ]));

        return $response
            ->withStatus(429)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-RateLimit-Limit', (string) $this->maxRequests)
            ->withHeader('X-RateLimit-Remaining', '0')
            ->withHeader('X-RateLimit-Reset', (string) $resetAt)
            ->withHeader('Retry-After', (string) $retryAfter);
    }
}
