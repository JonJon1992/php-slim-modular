<?php

namespace Core\Utils;

use Core\Contracts\ICrypt;
use Core\Middleware\CryptMiddleware;
use Core\Traits\Singleton;
use Exception;
use Hashids\Hashids;
use Illuminate\Hashing\BcryptHasher;
use JsonSerializable;

class Crypt implements ICrypt
{
    use Singleton;

    protected Hashids $hashids;
    protected BcryptHasher $bcrypt;

    public function __construct()
    {
        self::$singleton = $this;
        $this->bcrypt = new BcryptHasher(['verify' => true]);
        $this->hashids = new Hashids(env('APP_SALT_CRYPT'), 32);
    }

    /**
     * @return string
     */
    public function generate(): string
    {
        return base64_encode($this->hash(uniqid() . microtime()));
    }

    /**
     * @param $value
     * @return string
     */
    public function hash($value): string
    {
        return $this->bcrypt->make($value);
    }

    /**
     * @param $value
     * @param $hash
     * @return bool
     */
    public function check($value, $hash): bool
    {
        try {
            return $this->bcrypt->check($value, $hash);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function encode($value): string
    {
        $encoded = $this->hashids->encode($value);
        return (string) $encoded;
    }

    /**
     * @param string $value
     */
    public function decode(string $value)
    {
        return current($this->hashids->decode($value));
    }

    /**
     * @param array $data
     * @param array $keys
     * @param bool $encode
     */
    public function transform(array $data = [], array $keys = [], bool $encode = true): array
    {
        $key = array_shift($keys);

        if ($key === null || !isset($data[$key])) {
            return $data;
        }

        $value = $data[$key];

        // Fim do caminho ("a.b.id"): codifica/decodifica o valor escalar.
        if (!is_array($value)) {
            if (empty($keys)) {
                $data[$key] = $encode ? $this->encode($value) : $this->decode($value);
            }
            return $data;
        }

        // O caminho termina num array: mantém como está.
        if (empty($keys)) {
            return $data;
        }

        // Lista de registros: aplica o restante do caminho em cada um; objeto: aplica nele.
        if (array_is_list($value)) {
            foreach ($value as $k => $item) {
                if (is_array($item)) {
                    $data[$key][$k] = $this->transform($item, $keys, $encode);
                }
            }
        } else {
            $data[$key] = $this->transform($value, $keys, $encode);
        }

        return $data;
    }

    /**
     * @param $data
     * @param array $keys
     * @param bool $values
     */
    public function request($data, array $keys = [], bool $values = false)
    {
        if (!CryptMiddleware::$status) {
            return $data;
        }

        if (!is_array($data)) {
            return $this->decode($data);
        }
        if ($values) {
            return array_map(fn($item) => (int)$this->decode($item), $data);
        }
        return $this->doTransform($data, $keys, false);
    }

    /**
     * @param $data
     */
    public function requestValue($data)
    {
        if (!CryptMiddleware::$status) {
            return $data;
        }
        if (is_array($data)) {
            return array_map(fn($item) => (int)$this->decode($item), $data);
        }
        return $this->decode($data);
    }

    /**
     * @param $data
     * @param array $keys
     */
    public function response($data, array $keys = [])
    {
        if (!CryptMiddleware::$status) {
            return $data;
        }

        if ($data instanceof Paginator) {
            $data->setItems(array_map(fn($item) => $this->transformItem($item, $keys), $data->getItems()));
        } else if ($data instanceof JsonSerializable) {
            $data = $this->transformItem($data, $keys);
        } else if (is_array($data)) {
            $data = array_is_list($data)
                ? array_map(fn($item) => $this->transformItem($item, $keys), $data)
                : $this->doTransform($data, $keys);
        }
        return $data;
    }

    /**
     * @param mixed $item registro (array ou JsonSerializable); outros valores voltam sem alteração
     * @param array $keys
     * @return mixed
     */
    private function transformItem(mixed $item, array $keys): mixed
    {
        if ($item instanceof JsonSerializable) {
            $item = $item->jsonSerialize();
        }

        return is_array($item) ? $this->doTransform($item, $keys) : $item;
    }

    private function doTransform(array $data, array $keys, bool $encode = true): array
    {
        foreach ($keys as $key) {
            $data = $this->transform($data, explode('.', $key), $encode);
        }
        return $data;
    }
}
