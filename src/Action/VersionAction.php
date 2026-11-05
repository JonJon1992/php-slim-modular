<?php

namespace Core\Action;

use Core\App\Action;
use Core\Utils\Path;

class VersionAction extends Action
{
    /**
     * Versão declarada no composer.json do projeto; sem o campo "version" responde 204.
     */
    protected function action()
    {
        $file = Path::root('/composer.json');
        $composer = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;

        return is_array($composer) ? ($composer['version'] ?? null) : null;
    }

}