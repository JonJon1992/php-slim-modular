<?php

namespace Core\Middleware;

use Core\Traits\Create;
use Core\Utils\Time;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class CacheMiddleware
{
    use Create;

    const NAMESPACE = 'CACHE_MIDDLEWARE';

    protected bool $inactive = false;

    protected bool $byUser = false;

    protected int $expiresIn;

    /**
     * @param Request $request
     * @param Handler $handler
     * @return Response
     */
    public function invoke(Request $request, Handler $handler): Response
    {
        if ($this->inactive || $request->getMethod() != 'GET' || env('CACHE_MIDDLEWARE') != 'true') {
            return $handler->handle($request);
        }

        $key = $this->key($request);

        if (($cached = $this->read($key))) {
            $response = app()->getResponseFactory()
                ->createResponse()
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('X-Cache-Middleware', 'true');
            $response->getBody()->write($cached);
            return $response;
        }
        $response = $handler->handle($request);

        if ($response->getStatusCode() == StatusCodeInterface::STATUS_OK) {
            ob_start();
            $this->respond($request, $response);
            $data = ob_get_contents();
            ob_end_clean();
            $this->write($key, $data);
        }
        return $response->withHeader('X-Cache-Middleware', 'false');
    }

    public function getExpiresIn()
    {
        if (empty($this->expiresIn)) {
            $this->expiresIn = env('CACHE_MIDDLEWARE_EXPIRE', redis()->expireAtTheEndDay());
        }
        return $this->expiresIn;
    }


    /**
     * @param $response
     * @return bool|mixed
     */
    protected function isEmptyResponse(Response $response)
    {
        if (method_exists($response, 'isEmpty')) {
            return $response->isEmpty();
        }
        return in_array($response->getStatusCode(), [204, 205, 304]);
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function isHeadRequest(Request $request)
    {
        return strtoupper($request->getMethod()) === 'HEAD';
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function key(Request $request)
    {
        $key = [];
        $key[] = $this->byUser && auth()->id() ? auth()->id() : '0';
        $key[] = $request->getUri()->getPath();
        $key[] = md5($request->getUri()->getQuery() . $request->getBody()->getContents());

        return implode(':', $key);

    }

    /**
     * @param string $key
     */
    protected function read(string $key)
    {
        try {
            $key = explode(':', $key);
            $user_id = $key[0];
            $path = $key[1] . ':' . $key[2];
            return redis()->inDatabase(redis()->calcDbIndex($user_id), function () use ($user_id, $path) {
                $data = redis()->hGet(self::NAMESPACE . ':' . $user_id, $path);

                if ($data) {
                    $delimiter = strpos($data, '|');
                    $expiration = substr($data, 0, $delimiter);
                    $data = substr($data, $delimiter, 1);
                    if ($expiration < time()) {
                        redis()->hDel(self::NAMESPACE . ':' . $user_id, $path);
                    } else {
                        return $data;
                    }
                }
            });
        } catch (\RedisException) {
            return false;
        }
    }

    /**
     * @param string $key
     * @param string $data
     * @return false|mixed
     */
    protected function write(string $key, string $data)
    {
        try {
            $key = explode(':', $key);
            $user_id = $key[0];
            $path = $key[1] . ':' . $key[2];
            return redis()->inDatabase(redis()->calcDbIndex($user_id), function () use ($user_id, $path, $data) {
                $data = redis()->hSet(self::NAMESPACE . ':' . $user_id, $path, (time() + $this->getExpiresIn()) . '|' . $data);

                if (empty($user_id)) {
                    $ttl = redis()->ttl(self::NAMESPACE . ':' . $user_id);
                    if ($ttl && $ttl <= 0) {
                        redis()->expire(self::NAMESPACE . ':' . $user_id, redis()->expireAtTheEndDay());
                    }
                } else {
                    redis()->expire(self::NAMESPACE . ':' . $user_id, env('CACHE_MIDDLEWARE_EXPIRE_KEY', Time::_24H));
                }
            });

        } catch (\RedisException) {
            return false;
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function respond(Request $request, Response $response)
    {
        if (!headers_sent()) {
            foreach ($response->getHeaders() as $header => $values) {
                $first = !(stripos($header, 'Set-Cookie') === 0);
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $header, $value), $first);
                    $first = false;
                }
            }
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ), true, $response->getStatusCode());
        }
        if (!$this->isEmptyResponse($response) && !$this->isHeadRequest($request)) {
            $body = $response->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }
            $chuckSize = 4096;

            $contentLength = $response->getHeaderLine('Content-Length');
            if (!$contentLength) {
                $contentLength = $body->getSize();
            }
            if (isset($contentLength)) {
                $amountToRead = $contentLength;
                while ($amountToRead > 0 && !$body->eof()) {
                    $data = $body->read(min((int)$chuckSize, (int)$amountToRead));
                    echo $data;

                    $amountToRead -= strlen($data);

                    if (connection_status() != CONNECTION_NORMAL) {
                        break;
                    }
                }
            } else {
                while (!$body->eof()) {
                    echo $body->read((int)$chuckSize);
                    if (connection_status() != CONNECTION_NORMAL) {
                        break;
                    }
                }
            }
        }


    }
}