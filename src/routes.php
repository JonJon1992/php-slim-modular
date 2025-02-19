<?php
declare(strict_types=1);

use Core\Action\ImAliveAction;
use Core\App\App;
use Core\App\Module;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    $app->options('/{routes:.+}', fn($request, $response, $args) => $response);

    $app->get('/', function (Request $request, Response $response) {
        return [];
    });

    $app->get('/healty-check[/{service:.*}]', ImAliveAction::class);

    array_map(fn(Module $module) => $module->routes($app), $app->modules());
};