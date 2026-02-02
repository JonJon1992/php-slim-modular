<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Core\Utils\Arr;
use S3\Lib\S3;

trait StorageServiceTrait
{
    protected function registerStorageServices($configs): void
    {
        $default = $configs->get('storage.default', '');
        $storages = Arr::except($configs->get('storage', []), ['default', $default]);
        $storages = Arr::merge([$default => $configs->get("storage.$default", [])], $storages);

        foreach ($storages as $alias => $settings) {
            S3::singleton()->use($alias, $settings);
        }
    }
}
