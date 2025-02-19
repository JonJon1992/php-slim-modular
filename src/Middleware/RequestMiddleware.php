<?php

namespace Core\Middleware;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Server\RequestHandlerInterface as HandlerInterface;

class RequestMiddleware
{
    public static RequestInterface $request;

    /**
     * @param RequestInterface $request
     * @param HandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function __invoke(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        return $handler->handle(self::$request = $request);
    }

}