<?php

declare(strict_types=1);

use Core\App\App;
use Core\Utils\{Auth, Client, Config};
use Illuminate\Database\Connection;
use Slim\Views\PhpRenderer;

/**
 * @return App
 */
function app(): App
{
    return App::singleton();
}

function abort(string $message, int $code = 400, Exception $previous = null)
{
    throw  new Exception($message, $code, $previous);
}

/**
 * @param string $key
 * @param $default
 */
function config(string $key, $default = null)
{
    return Config::singleton()->get($key, $default);
}

/**
 * @param $key
 * @return mixed
 */
function service($key = null): mixed
{
    return app()->service($key);
}

/**
 * @param string|null $alias
 */
function logger(string $alias = null)
{
    $alias = $alias ?? app()->module() ?? config('log.default', array_key_first(config('log')));
    return service("log.$alias");
}

/**
 * @param string|null $alias
 */
function db(string $alias = null): Connection
{
    $alias = $alias ?? app()->module() ?? config('db.default', array_key_first(config('db')));
    return service("db.$alias");
}

/**
 * @param string|null $alias
 */
function redis(string $alias = null): \Core\Utils\Redis
{
    $alias = $alias ?? app()->module() ?? config('cache.default', array_key_first(config('cache')));
    return service("cache.$alias");

}

function auth(): Auth
{
    return Auth::singleton();
}

function view(): PhpRenderer
{
    return service('view');

}

function mailer(string $alias = null)
{
    $alias = $alias ?? app()->module() ?? config('mailer.default', array_key_first(config('mailer')));
    return service("mail.$alias");
}

/**
 * @return Client
 */
function client(): Client
{
    return Client::singleton();
}

