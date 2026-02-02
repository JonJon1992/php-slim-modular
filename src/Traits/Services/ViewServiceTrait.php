<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Core\Utils\{Path, Render\SlimPhpRenderAdapter};
use Slim\Views\PhpRenderer;

trait ViewServiceTrait
{
    protected function registerViewServices(array &$bindings, $configs): void
    {
        $bindings['view'] = function () use ($configs) {
            return new PhpRenderer($configs->get('app.view.folder'));
        };

        foreach (config('app.modules', []) as $class) {

            $module = new $class();
            $alias = $module->namespace();
            $dir = Path::modules('/' . $module->namespace() . $module->viewsFolder());

            if (!is_dir($dir)) {
                continue;
            }

            $bindings["view.$alias"] = function () use ($dir) {
                return new SlimPhpRenderAdapter($dir);
            };
        }
    }
}
