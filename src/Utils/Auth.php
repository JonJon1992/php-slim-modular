<?php
declare(strict_types=1);

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
    public function data(): ?array
    {
        return $this->data ?? null;
    }

    /**
     * @return string
     */
    public function raw(): string
    {
        return $this->raw;

    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function attr(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }


    /**
     * @return $this
     */
    public function logout(): static
    {
        $this->data = null;
        return $this;
    }

    /**
     * @return bool
     */
    public function on(): bool
    {
        return isset($this->data);
    }

    /**
     * @return bool
     */
    public function off(): bool
    {
        return !$this->on();
    }

    /**
     * @return string
     */
    public function ip($default = 'UNKNOWN'): string
    {
        $keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        foreach ($keys as $key) {
            if (isset($_SERVER[$key]) && $_SERVER[$key]) {
                return $_SERVER[$key];
            }
        }
        return $default;
    }

    /**
     * @param string $data
     */
    public function login(string $data): static
    {
        $this->raw = $data;
        $this->data = json_decode(base64_decode($data), true);
        return $this;
    }

    /**
     * @return string
     */
    public function agent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
}