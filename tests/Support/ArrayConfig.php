<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Support\Arr;

/**
 * Substitui o Core\Utils\Config (que lê a pasta configs/ do projeto) nos testes.
 */
final class ArrayConfig
{
    public function __construct(private array $items)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->items, $key, $default);
    }
}
