<?php

namespace Core\Utils;

use Core\Contracts\IClient;
use Core\Traits\Singleton;

class Client implements IClient
{
    use Singleton;

    private string $raw = '';

    private ?array $data;

    /**
     * @return string
     */
    public function raw(): string
    {
        return $this->raw;
    }

    /**
     * @return string|null
     */
    public function data(): ?array
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return self
     */
    public function use(string $data): IClient
    {
        $this->raw = $data;
        $this->data = json_decode(base64_decode($data), true);
        return $this;
    }

    /**
     * @return int|null
     */
    public function id(): ?int
    {
        return $this->data['id'] ?? null;
    }

    /**
     * @return string|null
     */
    public function key(): ?string
    {
        return $this->data['key'] ?? null;
    }

    /**
     * @return int|null
     */
    public function sessionGroupId(): ?int
    {
        return $this->data['session_group_id'] ?? null;
    }

    /**
     * @return string|null
     */
    public function version(): ?string
    {
        return $this->data['version'] ?? null;
    }
}