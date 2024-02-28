<?php

namespace Core\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class CryptMiddleware
{
    public static bool $status = false;

    /**
     * @param Request $request
     * @param Handler $handler
     * @return Response
     */
    public function __invoke(Request $request, Handler $handler): Response
    {
        if ($request->hasHeader('X-Crypt') && $request->getHeader('X-Crypt') == ['true']) {
            self::$status = true;
        }
        return $handler->handle($request);
    }

}