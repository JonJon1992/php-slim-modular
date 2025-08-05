<?php
declare(strict_types=1);

namespace Core\Middleware;

use Core\Contracts\IAuth;
use Core\Exceptions\Exception\AuthenticationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware
{
    protected IAuth $auth;

    public function __construct(IAuth $auth)
    {
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $auth = $request->hasHeader('x-auth') ? $request->getHeaderLine('x-auth') : '';

        if (empty($auth)) {
            throw new AuthenticationException();
        }

        $this->auth->login($auth);

        return $handler->handle($request);
    }
}