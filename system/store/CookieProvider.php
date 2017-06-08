<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/22 0022
 * Time: 11:24
 */

namespace App\System\Store;

use App\System\Basic\Provider;
use App\System\BasicInterface\StoreInterface;

class CookieProvider extends Provider implements StoreInterface
{
    protected static $cookiePath = "";
    protected static $cookieDomain = "";
    protected static $cookieExpire = 0;

    public function __construct(){
        parent::__construct();
    }

    /**
     * The first run the middleware.
     * @return mixed
     */
    public function middleware()
    {
        // TODO: Implement middleware() method.
    }

    /**
     * @throws \Exception
     */
    public function register()
    {
        // TODO: Implement register() method.

        if (!defined("COOKIE")){
            throw new \Exception("Const COOKIE undefined.");
        }

        self::setCookieDomain(COOKIE['cookieDomain']);
        self::setCookiePath(COOKIE['cookiePath']);
        self::setCookieExpire((int)COOKIE['cookieExpire'] + TIME_UTC);
    }

    /**
     * determine this key exist or not.
     * @param string $key
     * @return bool
     */
    public static function exist(string $key):bool
    {
        // TODO: Implement exist() method.
        return isset($_COOKIE[$key]);
    }

    /**
     * get value by key.
     * @param string $key
     * @return mixed
     */
    public static function get(string $key)
    {
        // TODO: Implement get() method.

        $val = $_COOKIE[$key];

        if (empty($val)){
            $val = null;
        }
        return $val;
    }

    /**
     * set value by key.
     * @param string $key
     * @param $val
     * @param int $expire
     * @return bool
     */
    public static function set(string $key, $val,int $expire):bool
    {
        // TODO: Implement set() method.
        if (empty($expire) == true){
            $expire = static::$cookieExpire;
        }else{
            $expire = TIME_UTC + $expire;
        }
        return self::save($key,$val,$expire,static::$cookiePath,static::$cookieDomain);
    }

    /**
     * delete a key.
     * @param string $key
     * @return bool
     */
    public static function remove(string $key):bool
    {
        // TODO: Implement remove() method.
        return self::save($key,'',0);
    }

    /**
     * clear all the keys.
     * @return bool
     */
    public static function destroy():bool
    {
        // TODO: Implement destroy() method.
        unset($_COOKIE);
        return true;
    }


    /**
     * save cookie.
     * @param string $key
     * @param string $val
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @return true
     */
    public static function save(string $key,string $val,int $expire = 0,string $path = '/',string $domain = '') {
        return setcookie($key, $val, $expire, $path, $domain);
    }


    /**
     * @param string $cookieDomain
     */
    protected static function setCookieDomain(string $cookieDomain)
    {
        self::$cookieDomain = $cookieDomain;
    }

    /**
     * @param int $cookieExpire
     */
    protected static function setCookieExpire(int $cookieExpire)
    {
        self::$cookieExpire = $cookieExpire;
    }

    /**
     * @param string $cookiePath
     */
    protected static function setCookiePath(string $cookiePath)
    {
        self::$cookiePath = $cookiePath;
    }

    /**
     * @return string
     */
    public static function getCookiePath(): string
    {
        return self::$cookiePath;
    }

    /**
     * @return string
     */
    public static function getCookieDomain(): string
    {
        return self::$cookieDomain;
    }

    /**
     * @return int
     */
    public static function getCookieExpire(): int
    {
        return self::$cookieExpire;
    }

}