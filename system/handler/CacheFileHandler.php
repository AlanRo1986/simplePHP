<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/22 0022
 * Time: 13:50
 */

namespace App\System\Handler;


use App\System\BasicInterface\CacheInterface;
use App\System\Utils\TextUtils;

class CacheFileHandler implements CacheInterface
{
    protected $storePath = null;
    protected $storeFileExtra = ".php";
    protected $cache = false;
    protected $expire = -1;

    public function __construct(array $args)
    {
        $this->setCache(conf("cache","enabled"));
        $this->setExpire($args['expire']);
        $this->setStorePath($args['path']);
        $this->init();
    }
    public function __destruct()
    {

    }

    /**
     * initialize the class.
     * @throws \Exception
     */
    public function init()
    {
        // TODO: Implement init() method.
        if (!is_dir($this->getStorePath())){
            throw new \Exception(sprintf("The [%s] is invalid directory.Please defined it in ConstantConfig:storage.",$this->getStorePath()));
        }

    }

    /**
     * determine this key exist or not.
     * @param string $key
     * @return bool
     */
    public function exist(string $key):bool
    {
        // TODO: Implement exist() method.

        $filename = $this->getCacheFileName($key);
        $content = @file_get_contents($filename);

        if( false === $content) {
            return false;
        }else{
            return true;
        }

    }

    /**
     * get value by key.
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        // TODO: Implement get() method.

        if ($this->cache == false){
            return false;
        }

        $var_key = md5($key);
        global $$var_key;
        if ($$var_key){
            return $$var_key;
        }

        $filename = $this->getCacheFileName($key);
        $content = @file_get_contents($filename);

        if( false !== $content) {
            $expire  =  (int)substr($content,8, 12);
            if($expire != -1 && time() > filemtime($filename) + $expire) {
                //缓存过期删除缓存文件
                @unlink($filename);
                return false;
            }
            $content = substr($content,20, -3);
            $content = unserialize($content);
            $$var_key = $content;
            return $content;
        }
        else {
            return false;
        }
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
        if($this->cache == false){
            return false;
        }

        $filename = $this->getCacheFileName($key);
        $data = "<?php\n//".sprintf('%012d',$this->expire).serialize($val)."\n?>";

        $rs = @file_put_contents($filename,$data);
        if($rs){
            return true;
        }else{
            return false;
        }
    }

    /**
     * delete a key.
     * @param string $key
     * @return bool
     */
    public function remove(string $key):bool
    {
        // TODO: Implement remove() method.

        if($this->cache == false){
            return false;
        }

        return unlink($this->getCacheFileName($key));

    }

    /**
     * clear all the keys.
     * @return bool
     */
    public function destroy():bool
    {
        // TODO: Implement destroy() method.

        $list = $this->getDirectoryFileList();
        if (is_array($list) && count($list) > 0){
            foreach ($list as $file){
                $fileName = $this->storePath . $file;
                @unlink($fileName);
            }
        }
        return true;
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
            return false;
        }
        $val = $this->get($key);

        return $this->set($key,(int)$val+$value);
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

        $val = $this->get($key);

        return $this->set($key,(int)$val + $value);
    }

    /**
     * get the file list.
     * @return array|bool
     */
    protected function getDirectoryFileList(){
        if(!is_dir($this->storePath)){
            return false;
        }

        $handle = opendir($this->storePath);
        $arrayFileName = [];
        while(($file = readdir($handle)) !== false)
        {
            if ($file != "." && $file != "..")
            {
                $len = 0 - strlen($this->storeFileExtra);
                if	(substr($file,$len) == $this->storeFileExtra)
                {
                    $arrayFileName[] = $file;
                }
            }

        }
        return $arrayFileName;

    }

    /**
     * get file name.
     * @param string $name
     * @return string
     * @throws \Exception
     */
    protected function getCacheFileName(string $name):string {

        if (TextUtils::isEmpty($name) == true){
            throw new \Exception("The param must be of input it.");
        }

        $name =	md5($name) . $this->getStoreFileExtra();

        $filename = $this->getStorePath() . $name;

        return $filename;
    }


    /**
     * @param string $storePath
     */
    public function setStorePath(string $storePath)
    {
        $this->storePath = app()->getRootPath() . $storePath;
    }

    /**
     * @return string
     */
    public function getStorePath(): string
    {
        return $this->storePath;
    }

    /**
     * @return string
     */
    public function getStoreFileExtra(): string
    {
        return $this->storeFileExtra;
    }

    /**
     * @param string $storeFileExtra
     */
    public function setStoreFileExtra(string $storeFileExtra)
    {
        $this->storeFileExtra = $storeFileExtra;
    }

    /**
     * @return array|bool|string
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return int|mixed
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @param array|bool|string $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param int|mixed $expire
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
    }
}