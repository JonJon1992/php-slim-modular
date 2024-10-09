<?php

namespace Core\Utils;

use Aws\Credentials\Credentials;

class SqsClient extends \Aws\Sqs\SqsClient
{
    private $client;

    public function __construct(array $configs)
    {
        $credentials = new Credentials($configs['key'], $configs['secret']);

        parent::__construct([
            'endpoint' => $configs['endpoint'],
            'region' => $configs['region'],
            'version' => $configs['version'],
            'credentials' => $credentials,
        ]);
    }

}