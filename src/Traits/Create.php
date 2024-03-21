<?php

namespace Core\Traits;

trait Create
{
    public static function create()
    {
        $class = get_called_class();
        return new $class();

    }

}