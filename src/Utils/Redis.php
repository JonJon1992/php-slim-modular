<?php

namespace Core\Utils;

use Closure;

class Redis extends \Redis
{
    public function __construct(array $config)
    {
        parent::__construct();
        $this->connect($config['host'], $config['port'], $config['timout']);
    }

    /**
     * @return int
     */
    public function expireAtTheEndDay()
    {
        return strtotime('tomorrow') - time();
    }

    /**
     * @param string $key
     * @param Closure $closure
     * @param int|null $expire
     * @param bool $crypt
     * @param bool $associative
     */
    public function getOrSet(string $key, Closure $closure, int $expire = null, bool $crypt = true, bool $associative = false)
    {
        $key = $crypt ? md5($key) : $key;
        $result = $this->get($key);

        if ($result) {
            return json_decode($result, $associative);
        }
        $result = $closure();
        $this->setex($key, $expire, json_encode($result));
        return $result;
    }

    /**
     * @param $id
     * @return int
     */
    public function calcDbIndex($id)
    {
        return $id <= 0 ? 0 : ($id % 15) + 1;
    }

    /**
     * @param $dbIndex
     * @param Closure $closure
     */
    public function inDatabase($dbIndex, Closure $closure)
    {
        $this->select($dbIndex);

        $result = $closure();

        $this->select(0);
        return $result;
    }

    /**
     * @param $dbIndex
     * @param $key
     * @param $value
     * @param $expire
     */
    public function setInDatabase($dbIndex, $key, $value, $expire)
    {
        return $this->inDatabase($dbIndex, fn() => $this->set($key, $value, $expire));
    }

    /**
     * @param $dbIndex
     * @param $key
     */
    public function getInDatabase($dbIndex, $key)
    {
        return $this->inDatabase($dbIndex, fn() => $this->get($key));
    }

    /**
     * @param $dbIndex
     * @param $key
     */
    public function delInDatabase($dbIndex, $key)
    {
        return $this->inDatabase($dbIndex, fn() => $this->del($key));

    }
}