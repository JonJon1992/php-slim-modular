<?php

namespace Core\Tests\Mocks;

use Core\Traits\Redis;

class RedisMock extends Redis
{
    private array $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        parent::__construct([
            "host" => '',
            "port" => '',
            "timeout" => '',
        ]);
    }

    /**
     * @param $key
     * @return false|string
     */
    public function get($key): false|string
    {
        if (isset($this->data[$key])) {
            return json_encode($this->data[$key]);
        }
        return json_encode($this->data);
    }

    public function set($key, $value, $timeout = null): true
    {
        $this->data[$key] = $value;
        return true;

    }

    /**
     * @param $host
     * @param $port
     * @param $timeout
     * @param $persistent_id
     * @param $retry_interval
     * @param $read_timeout
     * @param $context
     * @return true
     */
    public function connect(
        $host,
        $port = 6379,
        $timeout = 0,
        $persistent_id = null,
        $retry_interval = 0,
        $read_timeout = 0,
        $context = null
    ): true
    {
        return true;
    }
}