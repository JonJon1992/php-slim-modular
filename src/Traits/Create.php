<?php

namespace Core\Traits;

trait Create
{
    /**
     * @return mixed
     */
    public static function create(): mixed
    {
        $class = get_called_class();
        return new $class();
    }

}