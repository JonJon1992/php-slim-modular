<?php

namespace Core\Traits;

use Core\Utils\Auth;
use Core\Utils\Client;
use Core\Utils\Config;
use Core\Utils\Crypt;

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