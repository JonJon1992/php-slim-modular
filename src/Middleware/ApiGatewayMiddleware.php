<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Contracts\IAuth;
use Core\Contracts\IJwtValidator;
use Core\Exceptions\Exception\InvalidRequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Server\RequestHandlerInterface as HandlerInterface;

class ApiGatewayMiddleware
{
    private const CLIENT_HEADER = 'x-client';
    private const AUTH_HEADER = 'x-auth';

    private RequestInterface $request;
    private mixed $module = null;
    private ?array $auth = null;

    public function __construct(
        private readonly IJwtValidator $jwtValidator,
        private readonly IAuth $authService
    )
    {
    }

    public function __invoke(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        $this->request = $request;
        return $this->authRequest($handler);
    }

    private function authRequest(HandlerInterface $handler): ResponseInterface
    {
        $this->verifyJwt();

        $this->includeClient();
        $this->includeAuth();

        return $handler->handle($this->request)
            ->withoutHeader(self::AUTH_HEADER)
            ->withoutHeader(self::CLIENT_HEADER);
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
