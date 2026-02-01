<?php

namespace Core\Traits;

use Aws\Credentials\Credentials;

trait AwsClientBase
{
    protected array $configs;

    /**
     * @param array $configs
     * @return array
     */
    protected function buildAwsConfig(array $configs): array
    {
        $this->configs = $configs;
        $credentials = new Credentials($configs['key'], $configs['secret']);

        $config = [
            'region' => $configs['region'],
            'version' => $configs['version'],
            'credentials' => $credentials,
        ];

        if (!empty($configs['endpoint'])) {
            $config['endpoint'] = $configs['endpoint'];
        }

        return $config;
    }
}
