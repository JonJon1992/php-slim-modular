<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Core\Utils\{Path, Render\SlimPhpRenderAdapter};
use Slim\Views\PhpRenderer;

trait ViewServiceTrait
{
    protected function registerViewServices(array &$bindings, $configs, ?array $modules = null): void
    {
        $bindings['view'] = function () use ($configs) {
            return new PhpRenderer($configs->get('app.view.folder'));
        };

        $modules ??= array_map(fn(string $class) => new $class(), config('app.modules', []));

        foreach ($modules as $module) {
            $alias = $module->namespace();
            $dir = Path::modules('/' . $alias . $module->viewsFolder());

            if (!is_dir($dir)) {
                continue;
            }

            $bindings["view.$alias"] = function () use ($dir) {
                return new SlimPhpRenderAdapter($dir);
            };
        }
    }
}
