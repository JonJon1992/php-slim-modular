<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Contracts\IAuth;
use Core\Contracts\IJwtValidator;
use Core\Exceptions\Exception\InvalidRequestException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Server\RequestHandlerInterface as HandlerInterface;
use Core\Utils\Time;

class ApiGatewayMiddleware
{
    private const CLIENT_HEADER = 'x-client';
    private const CLIENT_PARAM = 'xclient';
    private const AUTH_HEADER = 'x-auth';

    private RequestInterface $request;
    private mixed $module = null;
    private ?array $auth = null;

    public function __construct(
        private IJwtValidator $jwtValidator,
        private IAuth $authService
    ) {
    }

    public function __invoke(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        $this->request = $request;
        return $this->authRequest($handler);
    }

    private function authRequest(HandlerInterface $handler): ResponseInterface
    {
        $this->verifyModuleSystem();
        $this->verifyJwt();

        $this->includeClient();
        $this->includeAuth();

        return $handler->handle($this->request)
            ->withoutHeader(self::AUTH_HEADER)
            ->withoutHeader(self::CLIENT_HEADER);
    }

    private function verifyModuleSystem(): void
    {
        $moduleKey = $this->getModuleKey();

        if (empty($moduleKey)) {
            return;
        }
        $this->module = $this->getModuleByKey($moduleKey);
    }

    private function verifyJwt(): void
    {
        $bearerToken = $this->getBearerToken();

        if (empty($bearerToken)) {
            return;
        }

        $claims = $this->jwtValidator->validateAndExtract($bearerToken);

        if ($claims === null) {
            throw new InvalidRequestException('Token inválido.');
        }

        $this->auth = $claims;

        $this->authService->login(base64_encode(json_encode($this->auth)));

        $this->request = $this->request
            ->withAttribute('auth.user_id', $claims['id'])
            ->withAttribute('auth.role', $claims['role'])
            ->withAttribute('auth.email', $claims['email'])
            ->withAttribute('auth.tenant_id', $claims['tid']);
    }

    private function getBearerToken(): ?string
    {
        $authorization = $this->request->getHeaderLine('Authorization');
        if (empty($authorization) || !preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            return null;
        }
        return trim($matches[1]);
    }

    private function getModuleKey(): ?string
    {
        if ($this->request->hasHeader(self::CLIENT_HEADER)) {
            return $this->request->getHeaderLine(self::CLIENT_HEADER);
        }

        $params = $this->request->getQueryParams();
        return isset($params[self::CLIENT_PARAM]) ? trim((string)$params[self::CLIENT_PARAM]) : null;
    }

    private function getModuleSystems(): mixed
    {
        $cacheKey = 'auth:module_client_keys';
        $redis = redis();

        $items = $redis->get($cacheKey);

        if (!empty($items)) {
            return json_decode($items, true);
        }

        $items = db()
            ->query()
            ->select()
            ->from('module_client_keys')
            ->get()
            ->toArray();

        $items = array_reduce($items, function (array $modules, $item) {
            $modules[$item->key] = (array)$item;
            return $modules;
        }, []);

        $redis->set($cacheKey, json_encode($items), Time::_24H);

        return $items;
    }

    private function getModuleByKey(string $key): mixed
    {
        $modules = $this->getModuleSystems();
        if (!array_key_exists($key, $modules)) {
            throw new InvalidArgumentException('Autenticação inválida');
        }

        return $modules[$key];
    }

    private function includeClient(): void
    {
        if (empty($this->module)) {
            $this->request = $this->request->withoutHeader(self::CLIENT_HEADER);
        } else {
            $this->request = $this->request->withHeader(self::CLIENT_HEADER, $this->raw($this->module));
        }
    }

    private function includeAuth(): void
    {
        if (empty($this->auth)) {
            $this->request = $this->request->withoutHeader(self::AUTH_HEADER);
        } else {
            $this->request = $this->request->withHeader(self::AUTH_HEADER, $this->raw($this->auth));
        }
    }

    private function raw(mixed $object): string
    {
        return base64_encode(json_encode($object));
    }
}
