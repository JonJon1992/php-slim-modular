<?php
declare(strict_types=1);

use Core\Action\HealtyCheckAction;
use Core\App\App;
use Core\App\SlimModule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    $app->options('/{routes:.+}', fn($request, $response, $args) => $response);

    $app->get('/', function (Request $request, Response $response) {
        return [];
    });

    $app->get('/healty-check[/{service:.*}]', HealtyCheckAction::class);

    array_map(fn(SlimModule $module) => $module->routes($app), $app->modules());
};