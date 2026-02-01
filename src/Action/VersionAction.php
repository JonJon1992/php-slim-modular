<?php

namespace Core\Action;

use Core\App\Action;
use Core\Traits\Path;

class VersionAction extends Action
{
    protected function action()
    {
        return json_decode(file_get_contents(Path::root('/composer.json')))->version;
    }

}