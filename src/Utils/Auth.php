<?php

namespace Core\Utils;

use Core\Contracts\IAuth;
use Core\Traits\Singleton;

class Auth implements IAuth
{
    use Singleton;

    private string $raw = '';
    private ?array $data;


    /**
     * @return int|null
     */
    public function id(): ?int
    {
        return $this->attr('id');
    }

    /**
     * @return array|null
     */
    public function data()
    {
        return $this->data ?? null;
    }

    /**
     * @return string
     */
    public function raw()
    {
        return $this->raw;

    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function attr(string $key)
    {
        return $this->data[$key] ?? null;
    }
}