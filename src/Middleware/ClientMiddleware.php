<?php

namespace Core\Middleware;

use Core\Contracts\IClient;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ClientMiddleware
{
    public function __construct(private IClient $client)
    {

    }

    /**
     * @throws Exception
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $client = $request->hasHeader('x-client') ? $request->getHeaderLine('x-client') : '';
        if (empty($client)) {
            throw new Exception('Client header missing', 400);
        }
        $this->client->use($client);
        return $handler->handle($request);

    }
}