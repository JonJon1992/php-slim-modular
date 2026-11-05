<?php
declare(strict_types=1);

namespace Core\Abstract;

use Core\App\Action;
use Core\Contracts\ICrypt;
use Core\Middleware\CryptMiddleware;
use Core\Utils\EncodedBase64;
use Core\Utils\Str;
use Fig\Http\Message\StatusCodeInterface;
use Illuminate\Support\Arr;
use JsonException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpInternalServerErrorException;

abstract class AbstractAction extends Action
{
    protected ICrypt $crypt;

    /**
     * Requisição para a qual $listParams foi calculado. A mesma instância da Action pode
     * atender várias requisições (container/testes), então o cache é refeito a cada uma.
     */
    private ?ServerRequestInterface $listParamsRequest = null;

    /**
     * @var array<string, string[]> listas da query string (columns, not-columns, columns-decoded)
     */
    private array $listParams = [];

    /**
     * @param $payload
     * @param int $status
     * @return Response
     */
    protected function respondWithJSON($payload, int $status = StatusCodeInterface::STATUS_OK): Response
    {
        try {
            $payload = $this->preparePayload($payload);
        } catch (JsonException $e) {
            throw new HttpInternalServerErrorException($this->request, 'Malformed JSON', $e);
        }

        return parent::respondWithJSON($payload, $status);
    }

    /**
     * Converte objetos (JsonSerializable, models) em arrays antes de aplicar columns/crypt.
     *
     * @param $payload
     * @return array|mixed
     * @throws JsonException
     */
    private function preparePayload($payload): mixed
    {
        $data = json_decode(json_encode($payload, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        return is_array($data) && $data !== [] ? $this->resolvePayload($data) : $data;
    }

    /**
     * @param array $data
     * @param bool $encode
     * @param string $prefix
     * @return array
     */
    protected function resolvePayload(array $data, bool $encode = true, string $prefix = ''): array
    {
        $dataReturn = [];

        $suffixDecoded = '_decoded';

        foreach ($data as $key => $value) {
            $keyDot = $prefix ?? '';

            if (!is_numeric($key)) {
                $keyDot = !empty($prefix) ? "{$prefix}.{$key}" : $key;
            }
            if ($encode && !$this->isParamSerializable($keyDot)) {
                continue;
            }
            $keyCheck = !is_numeric($key) ? $key : Arr::last(explode('.', $keyDot));

            $hasSuffixDecoded = false;
            if (is_string($key) && is_string($suffixDecoded)) {
                $hasSuffixDecoded = str_ends_with($key, $suffixDecoded);
            }
            $keyWithoutSuffixDecoded = $hasSuffixDecoded ? substr($key, 0, strlen($key) - strlen($suffixDecoded)) : $key;

            if (is_array($value)) {
                $keyReturn = !$encode && $hasSuffixDecoded ? $keyWithoutSuffixDecoded : $key;
                $dataReturn[$keyReturn] = $this->resolvePayload($value, $encode, $keyDot);
            }
            $isEncodedBase64 = !is_array($value) && is_string($key) && Str::endsWith($key, '_encoded_base64');
            $supportsHash = !is_array($value) && $this->attributeSupportHash($keyCheck);

            if (!is_array($value) && !$supportsHash && !$isEncodedBase64) {
                $dataReturn[$key] = $value;
            }
            if ($encode && $supportsHash) {
                $dataReturn[$key] = $this->encode($value);
                in_array($keyDot, $this->getCryptKeysDecoded()) && $dataReturn[$key . $suffixDecoded] = $value;
            }
            if (!$encode && $supportsHash) {
                $dataReturn[$key] = $this->decode($value);
            }
            if (!$encode && $isEncodedBase64) {
                $keyWithoutSuffix = $key;
                if (is_string($key) && str_ends_with($key, '_encoded_base64')) {
                    $keyWithoutSuffix = substr($key, 0, strlen($key) - strlen('_encoded_base64'));
                }
                $dataReturn[$keyWithoutSuffix] = EncodedBase64::create()->decode($value);
            }
            if (!is_array($value) && !$encode && $hasSuffixDecoded) {
                $dataReturn[$keyWithoutSuffixDecoded] = $value;
                unset($dataReturn[$key]);
            }

        }

        return $dataReturn;
    }

    /**
     * @return array
     */
    protected function getParsedBodyDecoded(): array
    {
        if ($body = $this->request->getParsedBody()) {
            $body = $body ? (array)$body : [];
        }
        return $this->resolvePayload($body ?? [], false);

    }

    /**
     * @return array
     */
    protected function getQueryParamsDecoded(): array
    {
        return $this->resolvePayload($this->request->getQueryParams(), false);

    }

    /**
     * @param string $key
     * @param $default
     * @return int|mixed|null
     */
    protected function getArgDecoded(string $key, $default = null): mixed
    {
        $arg = $this->arg($key);
        return $arg ? (int)$this->getCrypt()->requestValue($arg) : $default;

    }

    /**
     * @return ICrypt|mixed
     */
    protected function getCrypt(): mixed
    {
        $this->crypt = $this->crypt ?? service(ICrypt::class);
        return $this->crypt;
    }

    /**
     * @return array|string[]
     */
    public function getParamsNotColumns(): array
    {
        return $this->listQueryParam('not-columns');
    }

    /**
     * @return array|string[]
     */
    public function getParamsColumns(): array
    {
        return $this->listQueryParam('columns');
    }

    /**
     * @return array|string[]
     */
    public function getCryptKeysDecoded(): array
    {
        return $this->listQueryParam('columns-decoded', false);
    }

    /**
     * Lê uma lista separada por vírgula da query string uma única vez por requisição
     * (o resolvePayload consulta essas listas para cada chave do payload).
     *
     * @param string $name
     * @param bool $trimTrailingComma
     * @return string[]
     */
    private function listQueryParam(string $name, bool $trimTrailingComma = true): array
    {
        if ($this->listParamsRequest !== $this->request) {
            $this->listParamsRequest = $this->request;
            $this->listParams = [];
        }

        if (!array_key_exists($name, $this->listParams)) {
            $value = Arr::get($this->request->getQueryParams(), $name);
            $this->listParams[$name] = !empty($value) && is_string($value)
                ? explode(',', $trimTrailingComma ? rtrim($value, ',') : $value)
                : [];
        }

        return $this->listParams[$name];
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function attributeSupportHash(string $value): bool
    {
        return $value == 'id' || str_ends_with($value, '_id') || str_ends_with($value, '_ids');
    }


    /**
     * @param string $key
     * @return bool
     */
    protected function isParamSerializable(string $key): bool
    {
        $defaultPaginator = ['page', 'per_page', 'total', 'last_page'];

        if ($this->isResultPaginator() && in_array($key, $defaultPaginator)) {
            return true;
        }

        $isSerializable = true;

        if (!empty($keysSerializable = $this->getParamsColumns())) {
            $isSerializable = false;

            foreach ($keysSerializable as $keySerializable) {
                if ($key === $keySerializable || str_starts_with($keySerializable, $key . '.') || str_starts_with($key, $keySerializable . '.')) {
                    $isSerializable = true;
                }
            }
        }

        return (
            $isSerializable && (empty($this->getParamsNotColumns()) || !in_array($key, $this->getParamsNotColumns()))
        );
    }

    private function encode($value)
    {
        return CryptMiddleware::$status ? $this->getCrypt()->encode($value) : $value;
    }

    /**
     * @param $value
     */
    private function decode($value)
    {
        return CryptMiddleware::$status ? $this->getCrypt()->decode($value) : $value;

    }

    /**
     * @return false
     */
    protected function isResultPaginator(): false
    {
        return false;

    }

}