<?php

use Core\App\App;
use Core\App\SlimModule;

return function (App $app) {
    array_map(fn(SlimModule $module) => $module->subscribers($app), $app->modules());
};