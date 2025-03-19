<?php

namespace Core\Action;

use Core\App\Action;
use Core\Utils\Path;

class VersionAction extends Action
{
    protected function action()
    {
        return json_decode(file_get_contents(Path::root('/compose.json')))->version;
    }

}