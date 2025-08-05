<?php /** @noinspection ALL */

declare(strict_types=1);

use Core\App\App;
use Core\Exceptions\Exception\AppException;
use Core\Middleware\RequestMiddleware;
use Core\Utils\{Auth, Client, Config, Render\IRendebleAdapter, Trace};
use Illuminate\Database\Connection;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\PhpRenderer;

/**
 * @return App
 */
function app(): App
{
    return App::singleton();
}

/**
 * @throws AppException
 */
function abort(string $message, int $code = 400, \Exception $previous = null)
{
    throw new AppException($message, $code, $previous);
}

/**
 * @param string $key
 * @param null $default
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

/**
 * @return Auth
 */
function auth(): Auth
{
    return Auth::singleton();
}

/**
 * @return PhpRenderer
 */
function view(): PhpRenderer
{
    return service('view');

}

/**
 * @param string|null $alias
 * @return mixed
 */
function mailer(string $alias = null)
{
    $alias = $alias ?? app()->module() ?? config('mailer.default', array_key_first(config('mailer')));
    return service("mailer.$alias");
}

/**
 * @return Client
 */
function client(): Client
{
    return Client::singleton();
}

/**
 * @param string $name
 * @return string
 */
function keystore(string $name): string
{
    $file = realpath(Path::root('/keystore/') . "$name");
    return file_get_contents($file);
}

/**
 * @param string|null $alias
 * @return IRendebleAdapter
 */
function renderAdapter(string $alias = null): IRendebleAdapter
{
    $alias = $alias ?: config('view.folder');
    return service("view." . $alias);
}

/**
 * @return ServerRequestInterface
 */
function request(): ServerRequestInterface
{
    return RequestMiddleware::$request;
}

/**
 * @param array $config
 * @return \GuzzleHttp\Client
 */
function http(array $config = []): \GuzzleHttp\Client
{
    return Trace::http($config);

}

/**
 * @param string|null $alias
 * @return \Core\Utils\SqsClient
 */
function queue(string $alias = null)
{
    $alias = $alias ?? app()->module() ?? config('queue.default', array_key_first(config('queue')));
    return service("queue.$alias");
}

/**
 * @param string|null $alias
 * @return \Core\Utils\Dynamo
 */
function dynamo(string $alias = null): \Core\Utils\Dynamo
{
    $alias = $alias ?? app()->module() ?? config('dynamo.default', array_key_first(config('dynamo')));
    return service("dynamo.$alias");
}

/**
 * Dump and die - função de debug similar ao Laravel
 * 
 * @param mixed ...$vars Variáveis para fazer dump
 * @return void
 */
function dd(...$vars): void
{
    foreach ($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
    exit();
}

/**
 * Dump without die - função de debug que não para a execução
 * 
 * @param mixed ...$vars Variáveis para fazer dump
 * @return void
 */
function d(...$vars): void
{
    foreach ($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
}

