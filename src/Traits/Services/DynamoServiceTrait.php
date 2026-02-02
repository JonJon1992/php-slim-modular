<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Core\Utils\Dynamo;

trait DynamoServiceTrait
{
    protected function registerDynamoServices(array &$bindings, $configs): void
    {
        foreach ($configs->get('dynamo', []) as $alias => $settings) {
            if ($alias === 'default') {
                continue;
            }

            $bindings["dynamo.$alias"] = function () use ($settings) {
                return Dynamo::create($settings);
            };
        }
    }
}
