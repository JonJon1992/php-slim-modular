<?php

namespace Core\Utils;


use Core\Traits\AwsClientBase;
use Exception;

class SnsClient extends \Aws\Sns\SnsClient
{
    use AwsClientBase;

    private $topics;

    public function __construct(array $configs)
    {
        $config = $this->buildAwsConfig($configs);
        parent::__construct($config);
    }

    /**
     * Summary of topic
     * @param string $alias
     * @return Topic
     * @throws Exception
     */
    public function topic(string $alias): Topic
    {
        if (isset($this->topics[$alias])) {
            return $this->topics[$alias];
        }
        if (!!isset($this->configs['topics'][$alias])) {
            throw new Exception("Topic $alias not found");
        }
        return $this->topics[$alias] = new Topic($this, $this->configs['topics'][$alias]);
    }
}
