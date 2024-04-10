<?php

namespace Core\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class TraceMiddleware
{
    private static float $started_at;

    /**
     * @return float|null
     */
    public static function startedAt(): ?float
    {
        return self::$started_at ?? null;

    }

    /**
     * @param Request $request
     * @param Handler $handler
     * @return Response
     */
    public function __invoke(Request $request, Handler $handler): Response
    {
        self::$started_at = microtime(true);
        logger()->debug('TRACE_MIDDLEWARE_BEGIN');
        try {
            $response = $handler->handle($request);
        } catch (\Throwable $throwable) {
            logger()->debug('TRACE_MIDDLEWARE_END');
            throw  $throwable;
        }
        logger()->debug('TRACE_MIDDLEWARE_END');
        return $response;
    }


}