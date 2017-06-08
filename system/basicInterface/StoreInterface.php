<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/22 0022
 * Time: 12:55
 */

namespace App\System\BasicInterface;


interface StoreInterface
{
    /**
     * determine this key exist or not.
     * @param string $key
     * @return bool
     */
    public static function exist(string $key):bool ;

    /**
     * get value by key.
     * @param string $key
     * @return mixed
     */
    public static function get(string $key);

    /**
     * set value by key.
     * @param string $key
     * @param $val
     * @param int $expire
     * @return bool
     */
    public static function set(string $key,$val,int $expire):bool ;

    /**
     * delete a key.
     * @param string $key
     * @return bool
     */
    public static function remove(string $key):bool ;

    /**
     * clear all the keys.
     * @return bool
     */
    public static function destroy():bool ;

}