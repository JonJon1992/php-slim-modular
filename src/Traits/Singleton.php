<?php

declare(strict_types=1);

namespace Core\Traits;

trait Singleton
{
    private static ?self $singleton = null;

    public function __construct()
    {
        self::$singleton = $this;
    }

    public static function singleton(): self
    {
        return is_null(self::$singleton) ? new self() : self::$singleton;
    }

}