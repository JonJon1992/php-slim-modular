<?php

namespace Core\Utils;
use Core\Traits\Singleton;
use Illuminate\Support\Arr;

class Config
{
    use Singleton;

    protected array $configs = [];

    protected function __construct()
    {
        self::$singleton = $this;

        foreach (scandir(Path::configs()) as $config) {
            if (in_array($config, ['.', '..'])) {
                continue;
            }
            $this->configs[substr($config, 0, -4)] = require Path::configs() . "/$config";
        }
    }
    
    public function get(string $key, $default = null)
    {
        return Arr::get($this->configs, $key, $default);
    }
}
