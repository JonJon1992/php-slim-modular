<?php
declare(strict_types=1);

use Core\Action\ImAliveAction;
use Core\Action\VersionAction;
use Core\App\App;
use Core\App\Module;
use Core\Utils\Path;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

return function (App $app) {

    $app->options('/{routes:.+}', fn($request, $response, $args) => $response);

    $app->get('/', function (Request $request, Response $response) {
        return view()->render($response, 'index.phtml');
    });

    $app->get('/version', VersionAction::class);
    $app->get('/im-alive[/{service:.*}]', ImAliveAction::class);

    array_map(fn(Module $module) => $module->routes($app), $app->modules());

    $app->map(['*'], '/{routes:.+}', function ($request, $response) {
        throw new HttpNotFoundException($request);
    });

    if (env('APP_ENV') === 'production') {
        $app->getRouteCollector()->setCacheFile(Path::cache() . '/routes.cache.php');
    }
};