<?php

namespace Core\Contracts;

use Core\App\App;
use Core\Contracts\Event\IEventDispatcher;
use Core\Utils\Redis;
use Illuminate\Database\Connection;
use JsonSerializable;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouteGroupInterface;

interface ISlimModule
{
    function namespace(): string;

    function migrationFolder(): string;

    function commands(): array;

    function bindings(): array;

    function database(): Connection;

    function migrations(): array;

    function routes(App $app): RouteGroupInterface;

    function subscribers(App $app): void;

    function event(): IEventDispatcher;

    function eventSubscribers(App $app): void;

    function log(JsonSerializable $obj = null, $message = null): LoggerInterface;

    function redis(): Redis;

}
