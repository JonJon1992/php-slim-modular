<?php
declare(strict_types=1);

namespace Core\Middleware;

use Core\Traits\Create;
use Core\Utils\Time;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
class CacheMiddleware
{
    use Create;

    const NAMESPACE = 'CACHE_MIDDLEWARE';

    /**
     * Determina se o middleware está inativado.
     *
     * @var bool
     */
    protected bool $inactive = false;

    /**
     * Determina se o cache deve ser seccionado por usuário.
     * Isso adicionará o ID do usuário logado como sufixo da chave do cache.
     *
     * @var bool
     */
    protected bool $byUser = false;

    /**
     * Determina quando o cache deve expirar em segundos.
     * Por padrão, será adotada a quantidade de segundos restantes até o final do dia.
     *
     * @var int
     */
    protected int $expiresIn;

    /**
     * @return self
     */
    public static function createByUser(): self
    {
        return self::create()->byUser();
    }

    /**
     * @param Request $request
     * @param Handler $handler
     *
     * @return Response
     */
    public function __invoke(Request $request, Handler $handler): Response
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

        if ($response->getStatusCode() == 200) {
            ob_start();
            $this->respond($request, $response);
            $data = ob_get_contents();
            ob_end_clean();
            $this->write($key, $data);
        }

        return $response->withHeader('X-Cache-Middleware', 'false');
    }

    /**
     * Determina que a chave seja exclusiva do usuário.
     *
     * @return $this
     */
    public function byUser(): self
    {
        $this->byUser = true;
        return $this;
    }

    /**
     * Desativa o middleware
     *
     * @return $this
     */
    public function inactive(): self
    {
        $this->inactive = true;
        return $this;
    }

    /**
     * Configura a quantidde de segundos que a chave deve viver.
     *
     * @param int $seconds
     * @return $this
     */
    public function expiresIn(int $seconds): self
    {
        $this->expiresIn = $seconds;
        return $this;
    }

    /**
     * Recupera a quantidade de segundos que a chave deve viver.
     *
     * @return int
     */
    public function getExpiresIn(): int
    {
        if (empty($this->expiresIn)) {
            $this->expiresIn = env('CACHE_MIDDLEWARE_DEFAULT_EXPIRE', redis()->expiresAtTheEndDay());
        }
        return $this->expiresIn;
    }

    /**
     * @param $request
     * @param $response
     * @return void
     */
    public function respond($request, $response): void
    {
        if (!headers_sent()) {
            foreach ($response->getHeaders() as $name => $values) {
                $first = !(stripos($name, 'Set-Cookie') === 0);
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), $first);
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
            $chunkSize = 4096;

            $contentLength = $response->getHeaderLine('Content-Length');
            if (!$contentLength) {
                $contentLength = $body->getSize();
            }

            if (isset($contentLength)) {
                $amountToRead = $contentLength;
                while ($amountToRead > 0 && !$body->eof()) {
                    $data = $body->read(min((int)$chunkSize, (int)$amountToRead));
                    echo $data;

                    $amountToRead -= strlen($data);

                    if (connection_status() != CONNECTION_NORMAL) {
                        break;
                    }
                }
            } else {
                while (!$body->eof()) {
                    echo $body->read((int)$chunkSize);
                    if (connection_status() != CONNECTION_NORMAL) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param $response
     * @return bool|mixed
     */
    protected function isEmptyResponse($response): mixed
    {
        if (method_exists($response, 'isEmpty')) {
            return $response->isEmpty();
        }

        return in_array($response->getStatusCode(), [204, 205, 304]);
    }

    /**
     * @param $request
     * @return bool
     */
    protected function isHeadRequest($request): bool
    {
        return strtoupper($request->getMethod()) === 'HEAD';
    }

    /**
     * Retorna a chave de cache da rota.
     *
     * @param Request $request
     * @return string
     */
    protected function key(Request $request): string
    {
        $key = [];
        $key[] = $this->byUser && auth()->id() ? auth()->id() : '0';
        $key[] = $request->getUri()->getPath();
        $key[] = md5($request->getUri()->getQuery() . $request->getBody()->getContents());

        return implode(':', $key);
    }

    /**
     * Controla as leituras no redis/dynamo.
     *
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
                    $data = substr($data, $delimiter + 1);

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
     * Controla as escritas no redis/dynamo.
     *
     * @param string $key
     * @param string $data
     * @return false|void
     */
    protected function write(string $key, string $data)
    {
        try {
            $key = explode(':', $key);
            $user_id = $key[0];
            $path = $key[1] . ':' . $key[2];

            redis()->inDatabase(redis()->calcDbIndex($user_id), function () use ($user_id, $path, $data) {

                redis()->hSet(self::NAMESPACE . ':' . $user_id, $path, (time() + $this->getExpiresIn()) . '|' . $data);

                if (empty($user_id)) {
                    $ttl = redis()->ttl(self::NAMESPACE . ':' . $user_id);
                    if ($ttl && $ttl <= 0) {
                        redis()->expire(self::NAMESPACE . ':' . $user_id, redis()->expiresAtTheEndDay());
                    }
                } else {
                    redis()->expire(self::NAMESPACE . ':' . $user_id, env('CACHE_MIDDLEWARE_KEY_EXPIRE', Time::_24H));
                }
            });
        } catch (\RedisException $e) {
            return false;
        }
    }

    /**
     * @param  $patterns
     */
    public static function destroy($patterns): void
    {
        if (env('CACHE_MIDDLEWARE') !== true) {
            return;
        }

        $patterns = is_array($patterns) ? $patterns : [$patterns];

        foreach ($patterns as $pattern) {
            $key = explode(':', $pattern);
            $user_id = $key[0];
            $search = $key[1] . ':' . $key[2];

            redis()->inDatabase(redis()->calcDbIndex($user_id), function () use ($user_id, $search) {
                $args = [self::NAMESPACE . ':' . $user_id];

                $search = preg_replace('/\*/', '.*', preg_replace('/\//', '\\/', $search));

                foreach (redis()->hKeys(self::NAMESPACE . ':' . $user_id) as $key) {
                    if (preg_match("/$search/", $key)) {
                        $args[] = $key;
                    }
                }

                if (count($args) > 1) {
                    try {
                        redis()->hDel(...$args);
                    } catch (\Exception $e) {
                        logger()->error($e->getMessage());
                    }
                }
            });
        }
    }
}
