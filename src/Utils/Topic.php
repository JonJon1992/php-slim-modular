<?php

namespace Core\Utils;

class Topic
{
    private $arn;

    private $client;

    public function __construct(SnsClient $client, string $arn)
    {
        $this->client = $client;
        $this->arn = $arn;
    }

    /**
     * Summary of publish
     * @param array $attributes
     * @return mixed
     */
    public function publish(array $attributes = [])
    {
        $attributes['TopicArn'] = $this->arn;
        return $this->client->publish($attributes);
    }
}