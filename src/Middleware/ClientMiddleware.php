<?php

namespace Core\Middleware;

use Core\Contracts\IClient;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @deprecated desde 3.0.5, será removido na 4.0. Desde a v2.12.5 o ApiGatewayMiddleware
 *             remove o header x-client de toda requisição, então este middleware sempre
 *             responde 400.
 */
class ClientMiddleware
{
    protected IClient $client;

    public function __construct(IClient $client)
    {
        $this->client = $client;
    }

    /**
     * @throws Exception
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $client = $request->hasHeader('x-client') ? $request->getHeaderLine('x-client') : '';
        if (empty($client)) {
            throw new \Exception('Client key não informado....', 400);
        }
        $this->client->use($client);
        return $handler->handle($request);

    }
}