<?php
/**
 *
 * Queue push in.
 * $queue = app()->getInstance(QueueProvider::class);
 *
 * push expire time.
 * $queue->push("ids",10,5);
 *
 * push string|int value.
 * $queue->push("ids",11);
 *
 * push array|object value.
 * $queue->push("ids",["a","b","c"]);
 *
 * get the queue detail by key.
 * $queue->index("ids");
 *
 * get the queue size by key.
 * $queue->size("ids");
 *
 * get the last queue by key.
 * $queue->shift("ids");
 *
 * get the queue by key.
 * $queue->pop("ids");
 * ******************************
 *
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/26 0026
 * Time: 10:41
 */


namespace App\System\Store;


use App\System\Basic\Provider;
use App\System\BasicInterface\QueueInterface;
use App\System\Handler\RedisQueueHandler;
use App\System\Handler\SyncQueueHandler;

class QueueProvider extends Provider implements QueueInterface
{
    protected $params = [];
    protected $queue = null;

    /**
     * The first run the middleware.
     */
    public function middleware()
    {
        // TODO: Implement middleware() method.
    }

    /**
     * instance register.
     */
    public function register()
    {
        // TODO: Implement register() method.
        if (conf("queue","default") == "redis"){
            $this->params = array_merge(conf("queue","redis"),conf("redis",""));
            $this->setConnection(new RedisQueueHandler($this->params));
        }else{
            $this->setConnection(new SyncQueueHandler());
        }
    }

    /**
     * get queue detail by key.
     *
     * @param string $key
     * @param int $index
     * @return mixed|null
     */
    public function index(string $key,int $index = 0)
    {
        // TODO: Implement index() method.
        return $this->unMakeData($this->getConnection()->index($key,$index));
    }

    /**
     * get the queue size by key.
     * @param string $key
     * @return int|mixed
     */
    public function size(string $key)
    {
        // TODO: Implement size() method.
        return $this->getConnection()->size($key);
    }

    /**
     *
     * push queue
     * @param string $key
     * @param $data
     * @param int $expire
     * @return int|mixed
     */
    public function push(string $key, $data,int $expire = 0)
    {
        // TODO: Implement push() method.
        return $this->getConnection()->push($key,$this->makeData($data,$expire));
    }

    /**
     * @param string $key
     * @param $data
     * @param int $expire
     * @return int|mixed
     */
    public function first(string $key, $data,int $expire = 0)
    {
        // TODO: Implement last() method.
        return $this->getConnection()->first($key,$this->makeData($data,$expire));
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function pop(string $key)
    {
        // TODO: Implement pop() method.

        return $this->unMakeData($this->getConnection()->pop($key));
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function shift(string $key)
    {
        // TODO: Implement shift() method.
        return $this->unMakeData($this->getConnection()->shift($key));
    }

    /**
     * @return RedisQueueHandler
     */
    public function getConnection()
    {
        // TODO: Implement getConnection() method.
        return $this->queue;
    }

    /**
     * @param $name
     * @return mixed|void
     */
    public function setConnection($name)
    {
        // TODO: Implement setConnection() method.
        $this->queue = $name;
    }

    /**
     * @param $data
     * @param int $expire
     * @return string
     */
    private function makeData($data,int $expire = 0):string {

        $arr = [];
        $arr['data'] = $data;
        $arr['expire'] = $expire <= 0 ?  0 : $expire + time();

        return json_encode($arr);
    }

    /**
     * @param string $data
     * @return mixed|null
     */
    private function unMakeData(string $data){
        $arr = json_decode($data,true);
        if (is_array($arr) && isset($arr['expire'])){
            if ($arr['expire'] <= 0 || ($arr['expire'] > 0 && $arr['expire'] > time()) ){
                return $arr['data'];
            }
        }
        return null;
    }


}