<?php

declare(strict_types=1);

namespace Core\App;

use Core\Utils\Env;
use Fig\Http\Message\StatusCodeInterface;
use Illuminate\Support\Arr;
use JsonException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;

abstract class Action
{
    protected Request $request;
    protected Response $response;

    abstract protected function action();

    protected array $args;


    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $payload = $this->action();
        if (!isset($payload)) {
            return $this->respondWithoutContent();
        }
        if ($payload instanceof Response) {
            return $payload;
        }
        return $this->respondWithJSON($payload);
    }

    /**
     * @return Response
     */
    protected function respondWithoutContent(): Response
    {
        return $this->response->withHeader('Content-Type', 'application/json')
            ->withStatus(StatusCodeInterface::STATUS_NO_CONTENT);
    }

    /**
     * @param $payload
     * @param int $status
     * @return Response
     */
    protected function respondWithJSON($payload, int $status = StatusCodeInterface::STATUS_OK): Response
    {
        try {
            $json = json_encode($payload, $this->jsonFlags());
        } catch (JsonException $e) {
            throw new HttpInternalServerErrorException($this->request, 'Malformed JSON', $e);
        }
        $this->response->getBody()->write($json);

        return $this->response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    /**
     * JSON compacto em production/staging e formatado nos demais ambientes.
     * Acentos e barras saem sem escape (JSON_UNESCAPED_UNICODE/SLASHES), o que reduz o payload.
     *
     * @return int
     */
    protected function jsonFlags(): int
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

        return Env::real() ? $flags : $flags | JSON_PRETTY_PRINT;
    }

    /**
     * @param array $only
     * @return array
     */
    protected function args(array $only = []): array
    {
        return empty($only) ? $this->args : Arr::only($this->args, $only);
    }

    /**
     * @param string $key
     * @param $default
     */
    protected function arg(string $key, $default = null)
    {
        return Arr::get($this->args, $key, $default);
    }

    /**
     * @param string $key
     * @param $default
     */
    protected function param(string $key, $default = null)
    {
        return Arr::get($this->params(), $key, $default);
    }

    /**
     * @param array $only
     * @return array
     */
    protected function params(array $only = []): array
    {
        $params = $this->request->getParsedBody() ?? json_decode($this->request->getBody()->getContents(), true) ?? [];
        return empty($only) ? $params : Arr::only($params, $only);
    }

    /**
     * @param string $key
     * @param $default
     */
    protected function queryParam(string $key, $default = null)
    {
        return Arr::get($this->queryParams(), $key, $default);
    }

    /**
     * @param array $only
     * @return array
     */
    protected function queryParams(array $only = []): array
    {
        $params = $this->request->getQueryParams();
        return empty($only) ? $params : Arr::only($params, $only);
    }
}
