<?php

namespace Core\Utils;

use Aws\Credentials\Credentials;

class SnsClient extends \Aws\Sns\SnsClient
{
    private $topics;

    private array $configs;

    public function __construct(array $configs)
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
        parent::__construct($config);
    }

    /**
     * Summary of topic
     * @param string $alias
     * @throws \Exception
     * @return Topic
     */
    public function topic(string $alias)
    {
        if (isset($this->topics[$alias])) {
            return $this->topics[$alias];
        }
        if (!!isset($this->configs['topics'][$alias])) {
            throw new \Exception("Topic $alias not found");
        }
        return $this->topics[$alias] = new Topic($this, $this->configs['topics'][$alias]);
    }
}