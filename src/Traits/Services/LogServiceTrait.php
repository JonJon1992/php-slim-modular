<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Core\Utils\{Arr, Trace};
use Monolog\Formatter\JsonFormatter;
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
                $handler->setFormatter(new JsonFormatter());

                $logger = new Logger($alias);
                $logger->pushProcessor(fn($record) => Trace::processor($record));
                $logger->pushHandler($handler);
                return $logger;
            };
        }
    }
}
