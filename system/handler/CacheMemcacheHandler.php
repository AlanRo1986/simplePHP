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
use \Memcache;

class CacheMemcacheHandler implements CacheInterface
{
    protected $option = [];
    protected $expiration = 0;

    protected $cache = false;
    protected $prefix = "";
    protected $memcache = null;

    protected $logKeysFile = "public/cache/~cache_name.log";

    public function __construct(array $args)
    {
        $this->setCache(conf("cache","enabled"));
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

        if (!class_exists("Memcache")){
            throw new \Exception("Memcache is disable.");
        }

        if ($this->getCache() == true){
            $this->setExpiration($this->option['expire']);
            $this->connect();
        }
    }

    /**
     * connect the Memcache Object.
     */
    protected function connect(){
        $this->getMemcache()->connect($this->option['host'],$this->option['port']);
    }

    /**
     * close the connect.
     */
    protected function disconnect(){
        $this->getMemcache()->close();
    }

    /**
     * get the cache keyName.
     * @param string $key
     * @return string
     */
    protected function getKey(string $key):string {
        return $this->getPrefix().$key;
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

        return $this->getMemcache()->get($this->getKey($key));
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

        $this->saveLogKey($key);

        $key = $this->getKey($key);

        return $this->getMemcache()->set($key,$val,0,$this->getExpiration());

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

        return $this->getMemcache()->delete($this->getKey($key));
    }

    /**
     * clear all the keys.
     * @return bool
     */
    public function destroy():bool
    {
        // TODO: Implement destroy() method.
        $keys = $this->getLogKey();
        foreach($keys as $key) {
            $this->remove($key);
        }
        return $this->delLogKey();
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

        return $this->getMemcache()->increment($this->prefix.$key, $value);
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

        return $this->getMemcache()->decrement($this->prefix.$key, $value);
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
     * @param Memcache $memcache
     */
    public function setMemcache(Memcache $memcache)
    {
        $this->memcache = $memcache;
    }

    /**
     * @return Memcache
     */
    public function getMemcache(): Memcache
    {
        if ($this->memcache == null){
            $this->memcache = new Memcache();
        }

        return $this->memcache;
    }

    /**
     * @param int $expiration
     */
    public function setExpiration(int $expiration)
    {
        $this->expiration = $expiration = 0 ? -1 : $expiration;
    }

    /**
     * @return int
     */
    public function getExpiration(): int
    {
        return $this->expiration;
    }

    /**
     * @return array|bool|string
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param array|bool|string $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    public function getLogKeysFile(): string
    {
        return $this->logKeysFile;
    }

    /**
     * save the cache keys.maybe some function need it.
     * @param string $key
     */
    protected function saveLogKey(string $key)
    {
        $key_logs_files = app()->getRootPath().$this->getLogKeysFile();  //记录被缓存的名称

        if (!empty($key)){
            $keys = $this->getLogKey();

            if (in_array($key,$keys) == false){
                array_push($keys,$key);
            }
            @file_put_contents($key_logs_files,serialize($keys));
        }
    }

    /**
     * get the cache keys,return the array,if is null or type of other ,it return empty array.
     * @return array
     */
    protected function getLogKey():array
    {
        $key_logs_files = app()->getRootPath().$this->getLogKeysFile();   //记录被缓存的名称
        if(file_exists($key_logs_files))
        {
            $keys = @file_get_contents($key_logs_files);
            $keys = unserialize($keys);
            if(is_array($keys)) {
                return $keys;
            }
        }
        return [];
    }

    /**
     * delete the cache keyLog file.
     * @return bool
     */
    protected function delLogKey():bool
    {
        $key_logs_files = app()->getRootPath().$this->getLogKeysFile();   //记录被缓存的名称
        return @unlink($key_logs_files);
    }

}