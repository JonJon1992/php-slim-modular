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
use Psr\Http\Message\ResponseInterface as Response;

abstract class AbstractAction extends Action
{
    protected ICrypt $crypt;

    /**
     * @param $payload
     * @param int $status
     * @return Response
     */
    protected function respondWithJSON($payload, int $status = StatusCodeInterface::STATUS_OK): Response
    {
        return parent::respondWithJSON($this->preparePayload($payload), $status);
    }

    /**
     * @param $payload
     * @return array|mixed
     */
    private function preparePayload($payload): mixed
    {
        $data = json_decode(json_encode($payload, JSON_PRETTY_PRINT), true);
        return $data ? $this->resolvePayload($data) : $data;
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

            if ((!is_array($value) && !$this->attributeSupportHash($keyCheck)) && !$isEncodedBase64) {
                $dataReturn[$key] = $value;
            }
            if (!is_array($value) && $encode && $this->attributeSupportHash($keyCheck)) {
                $dataReturn[$key] = $this->encode($value);
                in_array($keyDot, $this->getCryptKeysDecoded()) && $dataReturn[$key . $suffixDecoded] = $value;
            }
            if (!is_array($value) && !$encode && $this->attributeSupportHash($keyCheck)) {
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
        $propertitiesNotSerializable = [];
        $keysNotSerializable = Arr::get($this->request->getQueryParams(), 'not-columns');

        if (!empty($keysNotSerializable) && is_string($keysNotSerializable)) {
            $propertitiesNotSerializable = explode(',', rtrim($keysNotSerializable, ','));
        }
        return $propertitiesNotSerializable;

    }

    /**
     * @return array|string[]
     */
    public function getParamsColumns(): array
    {
        $propertitiesSerializable = [];
        $keysSerializable = Arr::get($this->request->getQueryParams(), 'columns');

        if (!empty($keysSerializable) && is_string($keysSerializable)) {
            $propertitiesSerializable = explode(',', rtrim($keysSerializable, ','));
        }
        return $propertitiesSerializable;

    }

    /**
     * @return array|string[]
     */
    public function getCryptKeysDecoded(): array
    {
        $propertitiesCryptExcept = [];
        $keysDecodedParams = Arr::get($this->request->getQueryParams(), 'columns-decoded');

        if (!empty($keysDecodedParams) && is_string($keysDecodedParams)) {
            $propertitiesCryptExcept = explode(',', $keysDecodedParams);
        }
        return $propertitiesCryptExcept;

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