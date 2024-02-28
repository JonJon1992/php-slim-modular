<?php

namespace Core\App;

use DI\Container;
use Slim\Factory\AppFactory;
use Throwable;

class App extends \Slim\App
{

    protected ?SlimCommand $command = null;

    /**
     * @param Container|null $container
     * @return self
     */
    public static function singleton(Container $container = null): self
    {
        static $singleton = null;
        if (is_null($singleton)) {
            AppFactory::setContainer($container);
            $singleton = new self(AppFactory::determineResponseFactory(), $container);
        }
        return $singleton;
    }

    /**
     * @param string $key
     */
    public function service(string $key)
    {
        return $this->getContainer()->get($key);
    }

    /**
     * @return array|mixed
     */
    public function modules(): mixed
    {
        try {
            return $this->service('modules');
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return ?string
     */
    public function module(): ?string
    {
        static $current = '';
        if ($current != '') {
            return $current;
        }
        if (php_sapi_name() == 'cli' && $this->command) {
            return $current = explode(':', $this->command->getName()[0]);
        }
        $uri = explode('/', $_SERVER['REQUEST_URI'] ?? null);

        if (count($uri) < 2 || !is_string($uri[1])) {
            return $current = null;
        }
        foreach (app()->modules() as $module) {
            if ($module->namespace() == $uri[1]) {
                return $current = $uri[1];
            }
        }
        return $current = null;
    }

    /**
     * @param SlimCommand $command
     * @return void
     */
    public function command(SlimCommand $command): void
    {
        $this->command = $command;
    }

    /**
     * @return array
     */
    public function commands(): array
    {
        $commands = [];
        $container = app()->getContainer();

        foreach ($this->modules() as $module) {
            foreach ($module->commands() as $command) {
                $commands[] = $container->get($command);
            }
        }
        return $commands;
    }
}
