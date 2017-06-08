<?php
/**
 * https://pecl.php.net/
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/23 0023
 * Time: 10:04
 */

namespace App\System\Handler;


use App\System\BasicInterface\CacheInterface;
use \Memcached;

class CacheMemcachedHandler implements CacheInterface
{
    protected $option = [];
    protected $persistentId = null;
    protected $expiration = 0;

    protected $cache = false;
    protected $prefix = "";
    protected $memcached = null;

    public function __construct(array $args)
    {
        $this->setOption($args);
        $this->init();

    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }


    /**
     * initialize the class.
     */
    public function init()
    {
        // TODO: Implement init() method.
        if (count($this->getOption()) == 0){
            throw new \Exception("The [args] is params undefined.Please defined it in ConstantConfig:cache[memcached].");
        }

        if (!class_exists("Memcached")){
            throw new \Exception("Memcached is disable.");
        }

        $this->setPersistentId($this->option['persistent_id']);
        $this->setExpiration($this->option['expire']);
        $this->connect();
    }

    /**
     * connect the memcached.
     */
    protected function connect(){
        if ($this->getMemcached() == null && $this->getCache() == true){

            $this->setMemcached(empty($this->getPersistentId()) ? new Memcached() : new Memcached($this->getPersistentId()));

            $this->getMemcached()->addServer($this->option['host'],$this->option['port'],$this->option['weight']);

            /**
             * option:http://php.net/manual/zh/memcached.constants.php
             */
            $this->getMemcached()->setOption(Memcached::OPT_BINARY_PROTOCOL,true);
            $this->getMemcached()->setOption(Memcached::OPT_DISTRIBUTION,Memcached::DISTRIBUTION_CONSISTENT);
            //$this->getMemcached()->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE,true);

            if (!empty($this->option['options'])){
                $this->getMemcached()->setOptions($this->option['options']);
            }
        }
    }

    /**
     * disconnect the memcached.
     */
    protected function disconnect(){
        $this->getMemcached()->quit();
    }

    /**
     * determine this key exist or not.
     * @param string $key
     * @return bool
     */
    public function exist(string $key):bool
    {
        // TODO: Implement exist() method.
        if ($this->get($key) === false){
            return false;
        }

        return true;
    }

    /**
     * get value by key.
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        // TODO: Implement get() method.
        if ($this->getCache() == false){
            return false;
        }

        return $this->getMemcached()->get($this->getKey($key));
    }

    /**
     * get many caches.
     * @param array $keys
     * @return array|bool
     */
    public function getMany(array $keys)
    {
        if ($this->getCache() == false){
            return false;
        }

        $prefixedKeys = array_map(function ($key) {
            return $this->getKey($key);
        }, $keys);

        $values = $this->getMemcached()->getMulti($prefixedKeys, $null, Memcached::GET_PRESERVE_ORDER);

        if ($this->getMemcached()->getResultCode() != 0) {
            return array_fill_keys($keys, null);
        }
        return array_combine($keys, $values);
    }

    /**
     * set value by key.
     * @param string $key
     * @param $val
     * @return bool
     */
    public function set(string $key, $val):bool
    {
        // TODO: Implement set() method.

        if ($this->getCache() == false){
            return false;
        }

        return $this->getMemcached()->set($this->getKey($key), $val, $this->getExpiration());

    }

    /**
     * set many cache by array([key=>val])
     * @param array $values
     * @return bool
     */
    public function setMany(array $values):bool
    {
        if ($this->getCache() == false){
            return false;
        }

        $prefixedValues = [];

        foreach ($values as $key => $value) {
            $prefixedValues[$this->getPrefix().$key] = $value;
        }

        return $this->getMemcached()->setMulti($prefixedValues, $this->getExpiration());
    }

    /**
     * add a new cache.
     * @param string $key
     * @param $value
     * @return bool
     */
    public function add(string $key, $value):bool
    {
        if ($this->getCache() == false){
            return false;
        }

        return $this->getMemcached()->add($this->getKey($key), $value, $this->getExpiration());
    }

    /**
     * set the value increment by key.
     * @param string $key
     * @param int $value
     * @return int|bool
     */
    public function increment(string $key, int $value = 1)
    {
        if ($this->getCache() == false){
            return -1;
        }

        return $this->getMemcached()->increment($this->prefix.$key, $value);
    }

    /**
     * set the value decrement by key.
     * @param string $key
     * @param int $value
     * @return int|bool
     */
    public function decrement(string $key, int $value = 1)
    {
        if ($this->getCache() == false){
            return -1;
        }

        return $this->getMemcached()->decrement($this->prefix.$key, $value);
    }

    /**
     * delete a key.
     * @param string $key
     * @return bool
     */
    public function remove(string $key):bool
    {
        // TODO: Implement remove() method.

        if ($this->getCache() == false){
            return false;
        }

        return $this->getMemcached()->delete($this->getPrefix().$key);
    }

    /**
     * clear all the keys.
     * @return bool
     */
    public function destroy():bool
    {
        // TODO: Implement destroy() method.

        if ($this->getCache() == false){
            return false;
        }

        return $this->getMemcached()->flush();
    }

    /**
     * @param array $option
     */
    public function setOption(array $option)
    {
        $this->option = $option;
    }

    /**
     * @return array
     */
    public function getOption(): array
    {
        return $this->option;
    }

    /**
     * @param null $persistent_id
     */
    public function setPersistentId($persistent_id)
    {
        $this->persistentId = $persistent_id;
    }

    /**
     * @return null
     */
    public function getPersistentId()
    {
        return $this->persistentId;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param Memcached $memcached
     */
    public function setMemcached(Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

    /**
     * @return Memcached
     */
    public function getMemcached(): Memcached
    {
        if ($this->memcached = null){
            $this->memcached = new Memcached();
        }
        return $this->memcached;
    }

    /**
     * @param int $expiration
     */
    public function setExpiration(int $expiration)
    {
        $this->expiration = TIME_UTC + $expiration;
    }

    /**
     * @return int
     */
    public function getExpiration(): int
    {
        return $this->expiration;
    }

    /**
     * @param boolean $cache
     */
    public function setCache(bool $cache)
    {
        $this->cache = $cache;
    }

    /**
     * get the cache is enable or not.
     * @return bool
     */
    public function getCache():bool{
        return $this->cache;
    }

    /**
     * get the cache key(prefix.key).
     * @param string $key
     * @return string
     */
    public function getKey(string $key):string{
        return $this->getPrefix().$key;
    }
}