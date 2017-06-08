<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/26 0026
 * Time: 11:20
 */

namespace App\System\Handler;


use App\System\BasicInterface\QueueRedisInterface;

class RedisQueueHandler implements QueueRedisInterface
{
    protected $redis = null;
    protected $params = [];

    /**
     * RedisQueueHandler constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->redis = new \Redis();
        $this->connect();
    }

    /**
     * @param string $key
     * @param array|int|string $data
     * @return int
     */
    public function push(string $key, $data)
    {
        // TODO: Implement push() method.
        return $this->getRedis()->rPush($key,$data);
    }

    /**
     * @param string $key
     * @param $data
     * @return int
     */
    public function first(string $key, $data)
    {
        // TODO: Implement last() method.
        return $this->getRedis()->lPush($key,$data);
    }

    /**
     * @param string $key
     * @return string
     */
    public function pop(string $key)
    {
        // TODO: Implement pop() method.
        return $this->getRedis()->lPop($key);
    }

    /**
     * @param string $key
     * @return string
     */
    public function shift(string $key)
    {
        // TODO: Implement shift() method.
        return $this->getRedis()->rPop($key);
    }

    /**
     * @param string $key
     * @return int
     */
    public function size(string $key)
    {
        // TODO: Implement size() method.
        return $this->getRedis()->lLen($key);
    }

    /**
     * @param string $key
     * @param int $index
     * @return String
     */
    public function index(string $key,int $index = 0)
    {
        // TODO: Implement index() method.
        return $this->getRedis()->lIndex($key,$index);
    }

    /**
     * @return null|\Redis
     */
    public function getRedis():\Redis
    {
        return $this->redis;
    }

    /**
     * connect the redis
     */
    public function connect(){
        // TODO: Implement connect() method.
        //$host, $port = 6379
        $this->redis->pconnect($this->params['host'],$this->params['port']);
    }


    /**
     * close
     */
    public function disconnect()
    {
        // TODO: Implement disconnect() method.
        $this->getRedis()->close();
    }




}