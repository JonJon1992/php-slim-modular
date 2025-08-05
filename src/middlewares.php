<?php
declare(strict_types=1);

use Core\App\App;
use Core\Exceptions\ErrorHandler;
use Core\Middleware\{ApiGatewayMiddleware, CryptMiddleware, RequestMiddleware};
use Slim\Middleware\MethodOverrideMiddleware;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->add(MethodOverrideMiddleware::class);
    $app->add(RequestMiddleware::class);
    $app->add(ApiGatewayMiddleware::class);
    $app->add(CryptMiddleware::class);
    $app->addRoutingMiddleware();
    $app->addErrorMiddleware(config('app.debug'), config('app.error'), config('app.details'))
        ->setDefaultErrorHandler(new ErrorHandler($app->getCallableResolver(), $app->getResponseFactory()));
};
