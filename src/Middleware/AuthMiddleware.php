<?php

namespace Core\Middleware;

use Core\Contracts\IAuth;
use Core\Contracts\IClient;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware
{
    public function __construct(private IAuth $auth)
    {

    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $client = $request->hasHeader('x-auth-token') ? $request->getHeaderLine('x-auth-token') : '';
        if (empty($client)) {
            throw new Exception('User not authenticated.', 401);
        }
        $this->auth->login($client);
        return $handler->handle($request);
    }
}