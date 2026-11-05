<?php

declare(strict_types=1);

namespace Core\Middleware;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Server\RequestHandlerInterface as HandlerInterface;

class CryptMiddleware
{
    public static bool $status = false;

    /**
     * @param RequestInterface $request
     * @param HandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function __invoke(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        if ($request->hasHeader('X-Crypt') && $request->getHeader('X-Crypt') == ['true']) {
            self::$status = true;
        }
        return $handler->handle($request);
    }

}