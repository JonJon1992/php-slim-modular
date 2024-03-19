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
        return $this->hashids->encode($value);
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
    public function transform(array $data = [], array $keys = [], bool $encode = true)
    {
        $key = array_shift($keys);

        if (!isset($data[$key])) {
            return $data;
        }

        if (is_array($data[$key])) {
            if (count($keys) == 0) {
                return $data;
            }
        }
        foreach ($data[$key] as $k => $value) {
            if (is_array($value)) {
                $data[$key][$k] = $this->transform($data[$key][$k], $keys, $encode);
            } else {
                $data[$key] = $this->transform($data[$key], $keys, $encode);
            }
        }
        $data[$key] = $encode ? $this->encode($data[$key]) : $this->decode($data[$key]);
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
            $data->setItems(
                array_map(fn(Paginator $paginator) => $this->doTransform($paginator->jsonSerialize(), $keys), $data->getItems())
            );

        } else if ($data instanceof JsonSerializable) {
            $data = $this->doTransform($data->jsonSerialize(), $keys);
        } else if (is_array($data)) {
            $data = array_map(fn($item) => $item instanceof JsonSerializable ? $this->doTransform($item->jsonSerialize(), $keys) : $this->doTransform($item, $keys), $data);
        }
        return $data;

    }

    private function doTransform(array $data, array $keys, bool $encode = true)
    {
        foreach ($keys as $key) {
            $data = $this->transform($data, explode('.', $key), $encode);
        }
        return $data;

    }
}