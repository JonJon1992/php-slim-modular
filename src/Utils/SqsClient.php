<?php

namespace Core\Utils;

use Aws\Credentials\Credentials;

class SqsClient extends \Aws\Sqs\SqsClient
{
    public function __construct(array $configs)
    {
        $credentials = new Credentials($configs['key'], $configs['secret']);

        $config = [
            'region' => $configs['region'],
            'version' => $configs['version'],
            'credentials' => $credentials,
        ];

        // Add endpoint only if it's provided and not empty
        if (!empty($configs['endpoint'])) {
            $config['endpoint'] = $configs['endpoint'];
        }

        parent::__construct($config);
    }
}