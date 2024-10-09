<?php

namespace Core\Middleware;

use Core\Traits\Create;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class RateLimitMiddleware
{
    use Create;

    protected $limit;

    protected $interval;

    protected $blockTimeout;


    public function __invoke(Request $request, Handler $handler): int|Response
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'];
        $blockKey = 'rate_limit_block:' . $ip;
        $key = 'rate_limit:' . $ip;

        if (redis()->exists($blockKey)) {
            return app()
                ->getResponseFactory()
                ->createResponse()
                ->getBody()
                ->write([
                    "message" => 'IP temporarily blocked',
                    "statu_code" => 409
                ]);
        }

        $requests = redis()->get($key);

        if ($requests === false) {
            redis()->set($key, 1, $this->interval);
        } else {
            if ($requests >= $this->limit) {
                redis()->set($blockKey, 1, $this->blockTimeout);
                return app()
                    ->getResponseFactory()
                    ->createResponse()
                    ->getBody()
                    ->write([
                        "message" => 'IP temporarily blocked',
                        "statu_code" => 409
                    ]);
            }

            redis()->incr($key);
        }
        return $handler->handle($request);
    }


}