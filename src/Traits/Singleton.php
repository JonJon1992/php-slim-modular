<?php

namespace Core\Traits;

trait Singleton
{
    private static ?self $singleton = null;

    public function __construct()
    {
        self::$singleton = $this;
    }

    /**
     * @return Singleton
     */
    public static function singleton(): self
    {
        return is_null(self::$singleton) ? new self() : self::$singleton;
    }

}