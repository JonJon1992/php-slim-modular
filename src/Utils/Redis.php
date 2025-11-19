<?php

namespace Core\Utils;

use Closure;
use RedisException;
use Throwable;

/**
 * Class Redis
 * @package Glim\Utils
 *
 * @method bool isConnected()
 * @method false|string getHost()
 * @method false|int getPort()
 * @method bool|int getDbNum()
 * @method false|float getTimeout()
 * @method bool|float getReadTimeout()
 * @method bool|null|string getPersistentID()
 * @method bool|null|string getAuth()
 * @method bool close()
 * @method bool swapdb($db1, $db2)
 * @method bool setOption($option, $value)
 * @method mixed|null getOption($option)
 * @method bool|string ping($message = null)
 * @method string echo($message)
 * @method false|mixed|string get($key)
 * @method bool set($key, $value, $timeout = null)
 * @method bool setex($key, $ttl, $value)
 * @method bool psetex($key, $ttl, $value)
 * @method bool setnx($key, $value)
 * @method int del($key1, ...$otherKeys)
 * @method int unlink($key1, $key2 = null, $key3 = null)
 * @method Redis multi($mode = Redis::MULTI)
 * @method Redis pipeline()
 * @method void|array exec()
 * @method discard()
 * @method void watch($key)
 * @method void unwatch()
 * @method mixed|null subscribe($channels, $callback)
 * @method mixed|null psubscribe($patterns, $callback)
 * @method int publish($channel, $message)
 * @method array|int pubsub($keyword, $argument)
 * @method unsubscribe($channels = null)
 * @method punsubscribe($patterns = null)
 * @method int|bool exists($key)
 * @method int incr($key)
 * @method float incrByFloat($key, $increment)
 * @method int incrBy($key, $value)
 * @method int decr($key)
 * @method int decrBy($key, $value)
 * @method int|false lPush($key, ...$value1)
 * @method int|false rPush($key, ...$value1)
 * @method int|false lPushx($key, $value)
 * @method int|false rPushx($key, $value)
 * @method mixed|bool lPop($key)
 * @method mixed|bool rPop($key)
 * @method array blPop($keystore, $timeout)
 * @method array brPop(array $keystore, $timeout)
 * @method int|bool lLen($key)
 * @method mixed|bool lIndex($key, $index)
 * @method bool lSet($key, $index, $value)
 * @method array lRange($key, $start, $end)
 * @method array|false lTrim($key, $start, $stop)
 * @method int|bool lRem($key, $value, $count)
 * @method int lInsert($key, $position, $pivot, $value)
 * @method int|bool sAdd($key, ...$value1)
 * @method int sRem($key, ...$member1)
 * @method bool sMove($srcKey, $dstKey, $member)
 * @method bool sIsMember($key, $value)
 * @method int sCard($key)
 * @method string|mixed|array|bool sPop($key, $count = 1)
 * @method string|mixed|array|bool sRandMember($key, $count = 1)
 * @method array sInter($key1, ...$otherKeys)
 * @method int|false sInterStore($dstKey, $key1, ...$otherKeys)
 * @method array sUnion($key1, ...$otherKeys)
 * @method int sUnionStore($dstKey, $key1, ...$otherKeys)
 * @method array sDiff($key1, ...$otherKeys)
 * @method int|false sDiffStore($dstKey, $key1, ...$otherKeys)
 * @method array sMembers($key)
 * @method array|false sScan($key, &$iterator, $pattern = null, $count = 0)
 * @method string|mixed getSet($key, $value)
 * @method string randomKey()
 * @method bool select($dbIndex)
 * @method bool move($key, $dbIndex)
 * @method bool rename($srcKey, $dstKey)
 * @method bool renameNx($srcKey, $dstKey)
 * @method bool expire($key, $ttl)
 * @method bool pExpire($key, $ttl)
 * @method bool expireAt($key, $timestamp)
 * @method bool pExpireAt($key, $timestamp)
 * @method array keystore($pattern)
 * @method int dbSize()
 * @method bool auth($password)
 * @method bool bgrewriteaof()
 * @method bool slaveof($host = '127.0.0.1', $port = 6379)
 * @method mixed slowLog(string $operation, int $length = null)
 * @method string|int|false object($string = '', $key = '')
 * @method bool save()
 * @method bool bgsave()
 * @method int lastSave()
 * @method int wait($numSlaves, $timeout)
 * @method int type($key)
 * @method int append($key, $value)
 * @method string getRange($key, $start, $end)
 * @method int setRange($key, $offset, $value)
 * @method int strlen($key)
 * @method int bitpos($key, $bit, $start = 0, $end = null)
 * @method int getBit($key, $offset)
 * @method int setBit($key, $offset, $value)
 * @method int bitCount($key)
 * @method int bitOp($operation, $retKey, $key1, ...$otherKeys)
 * @method bool flushDB()
 * @method bool flushAll()
 * @method array sort($key, $option = null)
 * @method string info($option = null)
 * @method bool resetStat()
 * @method int|bool ttl($key)
 * @method int|bool pttl($key)
 * @method bool persist($key)
 * @method bool mset(array $array)
 * @method array mget(array $array)
 * @method int msetnx(array $array)
 * @method string|mixed|false rpoplpush($srcKey, $dstKey)
 * @method string|mixed|bool brpoplpush($srcKey, $dstKey, $timeout)
 * @method int zAdd($key, $options, $score1, $value1 = null, $score2 = null, $value2 = null, $scoreN = null, $valueN = null)
 * @method array zRange($key, $start, $end, $withscores = null)
 * @method int zRem($key, $member1, ...$otherMembers)
 * @method array zRevRange($key, $start, $end, $withscore = null)
 * @method array zRangeByScore($key, $start, $end, array $options = array())
 * @method array zRevRangeByScore($key, $start, $end, array $options = array())
 * @method array|bool zRangeByLex($key, $min, $max, $offset = null, $limit = null)
 * @method array zRevRangeByLex($key, $min, $max, $offset = null, $limit = null)
 * @method int zCount($key, $start, $end)
 * @method int zRemRangeByScore($key, $start, $end)
 * @method int zRemRangeByRank($key, $start, $end)
 * @method int zCard($key)
 * @method float|bool zScore($key, $member)
 * @method int|false zRank($key, $member)
 * @method int|false zRevRank($key, $member)
 * @method float zIncrBy($key, $value, $member)
 * @method int zUnionStore($output, $zSetKeys, array $weights = null, $aggregateFunction = 'SUM')
 * @method int zInterStore($output, $zSetKeys, array $weights = null, $aggregateFunction = 'SUM')
 * @method array|false zScan($key, &$iterator, $pattern = null, $count = 0)
 * @method array bzPopMax($key1, $key2, $timeout)
 * @method array bzPopMin($key1, $key2, $timeout)
 * @method array zPopMax($key, $count = 1)
 * @method array zPopMin($key, $count = 1)
 * @method int|bool hSet($key, $hashKey, $value)
 * @method bool hSetNx($key, $hashKey, $value)
 * @method string|false hGet($key, $hashKey)
 * @method int|false hLen($key)
 * @method int|bool hDel($key, $hashKey1, ...$otherHashKeys)
 * @method array hKeys($key)
 * @method array hVals($key)
 * @method array hGetAll($key)
 * @method bool hExists($key, $hashKey)
 * @method int hIncrBy($key, $hashKey, $value)
 * @method float hIncrByFloat($key, $field, $increment)
 * @method bool hMSet($key, $hashKeys)
 * @method array hMGet($key, $hashKeys)
 * @method array hScan($key, &$iterator, $pattern = null, $count = 0)
 * @method int hStrLen(string $key, string $field)
 * @method int geoadd($key, $longitude, $latitude, $member)
 * @method array geohash($key, ...$member)
 * @method array geopos(string $key, string $member)
 * @method float geodist($key, $member1, $member2, $unit = null)
 * @method mixed georadius($key, $longitude, $latitude, $radius, $unit, array $options = null)
 * @method array georadiusbymember($key, $member, $radius, $units, array $options = null)
 * @method array config($operation, $key, $value)
 * @method mixed eval($script, $args = array(), $numKeys = 0)
 * @method mixed evalSha($scriptSha, $args = array(), $numKeys = 0)
 * @method mixed script($command, $script)
 * @method string|null getLastError()
 * @method bool clearLastError()
 * @method mixed client($command, $value = '')
 * @method string _prefix($value)
 * @method mixed _unserialize($value)
 * @method mixed _serialize($value)
 * @method string|false dump($key)
 * @method bool restore($key, $ttl, $value)
 * @method bool migrate($host, $port, $key, $db, $timeout, $copy = false, $replace = false)
 * @method array time()
 * @method array|false scan(&$iterator, $pattern = null, $count = 0)
 * @method bool pfAdd($key, array $elements)
 * @method int pfCount($key)
 * @method bool pfMerge($destKey, array $sourceKeys)
 * @method mixed rawCommand($command, $arguments)
 * @method int getMode()
 * @method int xAck($stream, $group, $messages)
 * @method string xAdd($key, $id, $messages, $maxLen = 0, $isApproximate = false)
 * @method array xClaim($key, $group, $consumer, $minIdleTime, $ids, $options = [])
 * @method int xDel($key, $ids)
 * @method mixed xGroup($operation, $key, $group, $msgId = '', $mkStream = false)
 * @method mixed xInfo($operation, $stream, $group)
 * @method int xLen($stream)
 * @method array xPending($stream, $group, $start = null, $end = null, $count = null, $consumer = null)
 * @method array xRange($stream, $start, $end, $count = null)
 * @method array xRead($streams, $count = null, $block = null)
 * @method array xReadGroup($group, $consumer, $streams, $count = null, $block = null)
 * @method array xRevRange($stream, $end, $start, $count = null)
 * @method int xTrim($stream, $maxLen, $isApproximate)
 * @method int|bool sAddArray($key, array $values)
 */
class Redis extends \Redis
{
    private array $config;

    private ?\Redis $redis = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Summary of getRedis
     * @return \Redis|null
     */
    protected function getRedis(): \Redis
    {
        if ($this->redis === null) {
            $this->redis = new \Redis();
        }
        if (!$this->redis->isConnected()) {
            $this->connectInternal();
        }
        return $this->redis;
    }

    /**
     * Summary of __call
     * @param mixed $name
     * @param mixed $arguments
     */
    public function __call($name, $arguments)
    {
        try {
            return $this->getRedis()->$name(...$arguments);
        } catch (Throwable $t) {
            logger()->error($t->getMessage());
            throw $t;
        }
    }

    /**
     * Esta função retorna a quantidade de segundos restantes até o final do dia atual.
     *
     * @return int
     */
    public function expiresAtTheEndDay(): int
    {
        return strtotime('tomorrow') - time();
    }

    /**
     * Esta função aplica uma logica básica de utilização de cache.
     * Ela deve verificar se a $key informada existe e possui valor diferente de NULL.
     * Se verdadeiro o valor será retornado, caso contrário uma função será executada.
     * O retorno da função será armazenado em cache e tambem retornado.
     *
     * @param string $key
     * @param Closure $closure
     * @param int|null $expire
     * @param bool $crypt
     * @param bool $associative
     * @return mixed
     */
    public function getOrSet(string $key, Closure $closure, int $expire = null, bool $crypt = true, bool $associative = false): mixed
    {
        $key = $crypt ? md5($key) : $key;
        $result = $this->get($key);

        if ($result) {
            return json_decode($result, $associative);
        }

        $result = $closure();

        $this->setex($key, $expire, json_encode($result));

        return $result;
    }

    /**
     * Retorna o indice do banco de dados a ser utilizados com base em um ID.
     *
     * Para utilizar todos os bancos do redis (0 a 15), utilizamos o um ID como parametro de entrada, para definir
     * qual indice será utilizado.
     *
     * O resultado será sempre ZERO para IDs com valor MENOR OU IGUAL A ZERO.
     * Para valores positivos o indice será o MODULO da divisão do ID por 15, somado ao numero UM.
     *
     * Dessa forma os valores armazenados pode, de forma simples, serem balanceados entre os banco de dados.
     *
     * @param string|int $id
     * @return int
     */
    public function calcDbIndex($id): int
    {
        return $id <= 0 ? 0 : ($id % 15) + 1;
    }

    /**
     * Esta função executa uma outra função no contexto de um $dbindex específico e
     * depois volta para o $dbindex padrão igual a ZERO.
     *
     * @param string|int $dbIndex
     * @param Closure $closure
     * @return mixed
     */
    public function inDatabase($dbIndex, Closure $closure): mixed
    {
        $this->select($dbIndex);

        $result = $closure();

        $this->select(0);

        return $result;
    }

    /**
     * @param string|int $dbIndex
     * @param string $key
     * @param mixed $value
     * @param int $expire
     * @return mixed
     */
    public function setInDatabase($dbIndex, $key, $value, $expire): mixed
    {
        return $this->inDatabase($dbIndex, fn() => $this->set($key, $value, $expire));
    }

    /**
     * @param string|int $dbIndex
     * @param string $key
     * @return mixed
     */
    public function getInDatabase($dbIndex, $key): mixed
    {
        return $this->inDatabase($dbIndex, fn() => $this->get($key));
    }

    /**
     * @param string|int $dbIndex
     * @param string $key
     * @return mixed
     */
    public function delInDatabase($dbIndex, $key): mixed
    {
        return $this->inDatabase($dbIndex, fn() => $this->del($key));
    }

    protected function connectInternal(): void
    {
        try {
            $config = $this->config;
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? 6379;
            $timeout = $config['timeout'] ?? 10;

            $this->redis->connect($host, $port, $timeout);

            $password = $config['password'] ?? null;
            if (!empty($password)) {
                $this->redis->auth($password);
            }
        } catch (\RedisException $exception) {
            throw new RedisException($exception->getMessage());
        }
    }

}