<?php

use Core\App\App;
use Core\App\Module;

return function (App $app) {
    array_map(fn(Module $module) => $module->subscribers($app), $app->modules());
};