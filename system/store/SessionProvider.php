<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/22 0022
 * Time: 10:55
 */

namespace App\System\Store;


use App\System\Basic\Provider;
use App\System\BasicInterface\StoreInterface;

class SessionProvider extends Provider implements StoreInterface
{
    protected static $authKey = "";

    protected static $sessionExpire = 0;
    protected static $sessionPath = "";

    public function __construct()
    {
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
     * instance register.
     */
    public function register()
    {
        // TODO: Implement register() method.
        if (!defined("SESSION")){
            throw new \Exception("Const SESSION undefined.");
        }

        self::setAuthKey(conf("app","authToken"));
        self::setSessionExpire((int)SESSION['sessionExpire']);
        self::setSessionPath(SESSION['sessionPath']);

        self::start();
    }

    /**
     * return the session id.
     * @return string
     */
    public static function getSessionId()
    {
        return session_id();
    }

    /**
     * start the session.
     */
    public static function start()
    {
        session_set_cookie_params(0,CookieProvider::getCookiePath(),CookieProvider::getCookieDomain());
        session_save_path(self::getBasePath().self::getSessionPath());

        @session_start();
    }

    /**
     * close the session.
     */
    public static function close(){
        @session_write_close();
    }

    /**
     * return the session is expire or not.
     * @return bool
     */
    public static function isExpire():bool {
        if (self::exist("expire") && (int)self::get("expire") < TIME_UTC) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * set expire time.
     * @return bool
     */
    public static function setExpire():bool {
        return self::set("expire",TIME_UTC + self::getSessionExpire());
    }

    /**
     * determine this key exist or not.
     * @param string $key
     * @return bool
     */
    public static function exist(string $key):bool
    {
        // TODO: Implement exist() method.

        return isset($_SESSION[self::getEncryptKey($key)]);
    }

    /**
     * get value by key.
     * @param string $key
     * @return mixed
     */
    public static function get(string $key)
    {
        // TODO: Implement get() method.

        $val = $_SESSION[self::getEncryptKey($key)];

        if (empty($val)){
            return null;
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
    public static function set(string $key, $val,int $expire = 0):bool
    {
        // TODO: Implement set() method.

        $_SESSION[self::getEncryptKey($key)] = $val;

        return true;
    }

    /**
     * delete a key.
     * @param string $key
     * @return bool
     */
    public static function remove(string $key):bool
    {
        // TODO: Implement remove() method.
        unset($_SESSION[self::getEncryptKey($key)]);
        return true;
    }

    /**
     * clear all the keys.
     * @return bool
     */
    public static function destroy():bool
    {
        // TODO: Implement destroy() method.

        return session_destroy();
    }

    /**
     * return auth&key.
     * @param string $key
     * @return string
     */
    protected static function getEncryptKey(string $key):string {
        return self::getAuthKey()."_".$key;
    }

    /**
     * @param string $authKey
     */
    protected static function setAuthKey(string $authKey)
    {
        self::$authKey = $authKey;
    }

    /**
     * @param int $sessionExpire
     */
    protected static function setSessionExpire(int $sessionExpire)
    {
        self::$sessionExpire = $sessionExpire;
    }

    /**
     * @param string $sessionPath
     */
    protected static function setSessionPath(string $sessionPath)
    {
        self::$sessionPath = $sessionPath;
    }

    /**
     * @return string
     */
    public static function getAuthKey(): string
    {
        return self::$authKey;
    }

    /**
     * @return int
     */
    public static function getSessionExpire(): int
    {
        return self::$sessionExpire;
    }

    /**
     * @return string
     */
    public static function getSessionPath(): string
    {
        return self::$sessionPath;
    }


}