<?php

namespace Core\App;

use Core\Contracts\Event\IEventDispatcher;
use Core\Contracts\IModule;
use Core\Event\EventDispatcher;
use Core\Utils\Email;
use Core\Utils\Path;
use Core\Utils\Redis;
use Core\Utils\Render\IRendebleAdapter;
use Illuminate\Database\Connection;
use JsonSerializable;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use S3\Lib\Bucket;
use Slim\Interfaces\RouteCollectorProxyInterface as Route;
use Slim\Interfaces\RouteGroupInterface;
use function DI\autowire;

abstract class Module implements IModule
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

    /**
     * @var string
     */
    protected string $views_folder = '/Views';

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

    public function viewsFolder(): string
    {
        return $this->views_folder;
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
        return array_map(function ($value) {
            return autowire($value);
        }, $this->bindings);
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
    public function s3(): Bucket
    {
        return s3($this->namespace());
    }

    public function mail(): Email
    {
        return Email::create()
            ->setMailer($this->mailer())
            ->setRender($this->renderAdapter());

    }

    public function mailer()
    {
        return mailer($this->namespace());
    }

    public function renderAdapter(): IRendebleAdapter
    {
        return renderAdapter($this->namespace());
    }
}