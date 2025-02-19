<?php

namespace Core\Middleware;

use Core\Contracts\IClient;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ClientMiddleware
{
    protected IClient $client;

    public function __construct(IClient $client)
    {
        $this->client = $client;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $client = $request->hasHeader('x-client') ? $request->getHeaderLine('x-client') : '';
        if (empty($client)) {
            throw new \Exception('Client não informado.', 400);
        }
        $this->client->use($client);
        return $handler->handle($request);

    }
}