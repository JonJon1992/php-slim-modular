<?php

namespace Core\Utils;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\Driver\Server;

class Mongo
{
    protected Client $client;

    protected Server $server;

    public function __construct(array $config)
    {
        $this->client = new Client($config['driver'] . "://" . $config['username_master'] . ":" . $config['password_master'] . "@" . $config['host'] . ":" . $config['port'] . "?retryWrites=false");
    }

    /**
     * @param string $database
     * @return Database
     */
    public function getDatabase(string $database)
    {
        return $this->client->selectDatabase($database);
    }

    /**
     * @param string|null $collection
     * @return Collection
     */
    public function collection(string $collection = null): Collection
    {
        return $this->client->selectCollection($collection);
    }

    /**
     * @return array
     */

    public function info()
    {
        return $this->server->getInfo();

    }
}
