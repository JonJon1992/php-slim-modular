<?php

namespace Core\Traits;

trait Create
{
    /**
     * @return self
     */
    public static function create(): self
    {
        $class = get_called_class();
        return new $class();
    }

}