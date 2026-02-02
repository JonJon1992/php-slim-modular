<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Core\Utils\Redis;

trait CacheServiceTrait
{
    protected function registerCacheServices(array &$bindings, $configs): void
    {
        foreach ($configs->get('cache', []) as $alias => $settings) {
            if ($alias === 'default') {
                continue;
            }
            $bindings["cache.$alias"] = function () use ($settings) {
                return new Redis($settings);
            };
        }
    }
}
