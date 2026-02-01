<?php

use Core\App\App;
use Core\Commands\MigrateCreateCommand;
use Core\Commands\MigrateRunCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/src/bootstrap.php';
/**
 * @var App $app
 */
$cli = new Application();

$cli->addCommands($app->commands());

$cli->add(new MigrateCreateCommand());
$cli->add(new MigrateRunCommand());
$cli->add(new \Core\Commands\QueueWorkCommand());

$cli->run();
