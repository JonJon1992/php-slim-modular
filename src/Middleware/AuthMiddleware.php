<?php

namespace Core\Middleware;

use Core\Contracts\IAuth;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware
{
    public function __construct(private IAuth $auth)
    {

    }

    /**
     * @throws Exception
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $auth = $request->hasHeader('x-auth') ? $request->getHeaderLine('x-auth') : '';
        if (empty($auth)) {
            throw new Exception('O usuário não está autenticado.', 401);
        }
        $this->auth->login($auth);
        return $handler->handle($request);
    }
}