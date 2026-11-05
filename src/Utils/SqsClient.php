<?php

declare(strict_types=1);

namespace Core\Utils;

use Core\Traits\AwsClientBase;

class SqsClient extends \Aws\Sqs\SqsClient
{
    use AwsClientBase;

    public function __construct(array $configs)
    {
        $config = $this->buildAwsConfig($configs);
        parent::__construct($config);
    }
}
