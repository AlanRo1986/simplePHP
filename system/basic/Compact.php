<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/5/19 0019
 * Time: 21:37
 */

namespace App\System\Basic;


abstract class Compact extends Logger
{
    const configPath = "system/config/";

    protected $__setArray = [];
    protected static $basePath = ROOT_PATH;

    public function __construct()
    {

    }

    public function __call(string $name, $arguments)
    {
        // TODO: Implement __call() method.
        app()->invalidMethod($name,get_called_class());
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    public function getClass():string {
        return get_called_class();
    }

    public function __toString():string
    {
        // TODO: Implement __toString() method.

    }

    public function __isset(string $name):bool
    {
        // TODO: Implement __isset() method.
        return isset($this->__setArray[$name]);
    }

    public function __unset(string $name)
    {
        // TODO: Implement __unset() method.

        unset($this->__setArray[$name]);
    }

    public function __sleep()
    {
        // TODO: Implement __sleep() method.
    }

    public function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public function __set(string $name, $value)
    {
        // TODO: Implement __set() method.

        $this->__setArray[$name] = $value;
    }

    public function __get(string $name)
    {
        // TODO: Implement __get() method.

        return $this->__setArray[$name];
    }

    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

    public function __debugInfo()
    {
        // TODO: Implement __debugInfo() method.
    }

    public function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    protected static function setBasePath(string $path)
    {
        static::$basePath = $path;
    }

    /**
     * @return string
     */
    protected static function getBasePath():string
    {
        return self::$basePath;
    }
}