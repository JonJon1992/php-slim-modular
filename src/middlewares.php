<?php

use Core\App\App;
use Core\Exceptions\ErrorHandler;
use Core\Middleware\{CryptMiddleware, GatewayMiddleware, RequestMiddleware};
use Slim\Middleware\MethodOverrideMiddleware;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->add(MethodOverrideMiddleware::class);
    $app->add(RequestMiddleware::class);
    $app->add(GatewayMiddleware::class);
    $app->add(CryptMiddleware::class);
    $app->addRoutingMiddleware();
    $app->addErrorMiddleware('app.debug', config('app.error'), config('app.details'))
        ->setDefaultErrorHandler(new ErrorHandler($app->getCallableResolver(), $app->getResponseFactory()));
};