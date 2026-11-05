<?php

namespace Core\Utils;

use Core\Contracts\IClient;
use Core\Traits\Singleton;

/**
 * @deprecated desde 3.0.5, será removido na 4.0. O ApiGatewayMiddleware deixou de repassar
 *             o header x-client na v2.12.5.
 */
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
     * @return array|null
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
     * @return string|null
     */
    public function id(): ?string
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
     * @return string|null
     */
    public function sessionGroupId(): ?string
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