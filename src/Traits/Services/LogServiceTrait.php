<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Core\Utils\{Arr, TraceJsonFormatter};
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

trait LogServiceTrait
{
    protected function registerLogServices(array &$bindings, $configs): void
    {
        foreach ($configs->get('log', []) as $alias => $settings) {
            if ($alias === 'default') {
                continue;
            }
            $bindings["log.$alias"] = function () use ($alias, $settings) {

                $path = Arr::get($settings, 'path');

                $handler = new StreamHandler($path, config('app.log_level'));
                $handler->setFormatter(new TraceJsonFormatter());

                $logger = new Logger($alias);
                $logger->pushHandler($handler);
                return $logger;
            };
        }
    }
}
