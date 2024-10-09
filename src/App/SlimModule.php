<?php

namespace Core\App;

use Core\Contracts\Event\IEventDispatcher;
use Core\Contracts\ISlimModule;
use Core\Event\EventDispatcher;
use Core\Utils\Path;
use Core\Utils\Redis;
use Illuminate\Database\Connection;
use JsonSerializable;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use S3\Lib\Bucket;
use Slim\Interfaces\RouteCollectorProxyInterface as Route;
use Slim\Interfaces\RouteGroupInterface;
use function DI\autowire;

abstract class SlimModule implements ISlimModule
{
    /**
     * @var string
     */
    protected string $namespace = '_MODULE_';

    /**
     * @var bool
     */
    protected bool $namespace_router = true;

    /**
     * @var string
     */
    protected string $migration_folder = '/Infrastructure/Migrations';

    /**
     * @var array
     */
    protected array $commands = [];

    /**
     * @var array
     */
    protected array $bindings = [];

    /**
     * @var array
     */
    protected array $middleware = [];

    /**
     * @var IEventDispatcher
     */
    protected IEventDispatcher $dispatcher;

    /**
     * @var array
     */
    protected array $subscribers = [];

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
    }

    /**
     * @return string
     */
    public function namespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return array
     */
    function commands(): array
    {
        return $this->commands;
    }

    /**
     * @return array
     */
    function bindings(): array
    {
        $list = [];
        foreach ($this->bindings as $key => $value) {
            $list[$key] = autowire($value);
        }
        return $list;
    }

    /**
     * @return Connection
     */
    function database(): Connection
    {
        return db($this->namespace());
    }

    /**
     * @return array
     */
    function migrations(): array
    {
        $folder = Path::modules() . '/' . $this->namespace() . $this->migrationFolder();

        if (!is_dir($folder)) {
            return [];
        }
        $migrations = [];

        $moduleReflect = new ReflectionClass($this);

        $files = scandir($folder);

        foreach ($files as $file) {
            if (str_starts_with($file, '.')) {
                continue;
            }
            $className = $moduleReflect->getNamespaceName() . str_replace('/', '\\', $this->migrationFolder()) . '\\' . str_replace('.php', '', $file);

            $migrations[] = $className;
        }
        return $migrations;
    }

    /**
     * @param App $app
     * @return RouteGroupInterface
     */
    function routes(App $app): RouteGroupInterface
    {
        $me = $this;
        $namespace = $this->namespace_router ? '/' . $this->namespace : '';

        $group = $app->group($namespace, function (Route $route) use ($me) {
            $route->group('/v1', fn(Route $route) => $me->v1($route));
            $route->group('/v2', fn(Route $route) => $me->v2($route));
        });
        foreach ($this->middleware as $middle) {
            $group->add($middle);
        }
        return $group;
    }

    /**
     * @param App $app
     * @return void
     */
    function subscribers(App $app): void
    {
        $container = $app->getContainer();
        foreach ($this->subscribers as $subscriber) {
            $this->dispatcher->addSubscriber($container->get($subscriber));
        }
    }

    public function event(): IEventDispatcher
    {
        return $this->dispatcher;
    }

    public function eventSubscribers(App $app): void
    {
        $container = $app->getContainer();
        foreach ($this->subscribers as $subscriber) {
            $sub = $container->get($subscriber);
            $this->dispatcher->addSubscriber($container->get($sub));
        }
    }

    /**
     * @param JsonSerializable|null $obj
     * @param $message
     * @return LoggerInterface
     */
    function log(JsonSerializable $obj = null, $message = null): LoggerInterface
    {
        $logger = logger($this->namespace());
        if ($obj) {
            $logger->critical($message, $obj->jsonSerialize());
        }
        return $logger;
    }

    /**
     * @return string
     */
    public function migrationFolder(): string
    {
        return $this->migration_folder;
    }

    /**
     * @param Route $v1
     * @return void
     */

    public function v1(Route $v1)
    {

    }

    /**
     * @param Route $v2
     * @return void
     */
    public function v2(Route $v2)
    {

    }

    public function redis(): Redis
    {
        return redis($this->namespace());
    }

    /**
     * @return Bucket
     */
    public function s3()
    {
        return s3($this->namespace());
    }

    public function mailer()
    {
        return mailer($this->namespace);
    }
}