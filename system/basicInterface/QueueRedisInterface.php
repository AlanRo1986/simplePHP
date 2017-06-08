<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/26 0026
 * Time: 11:24
 */

namespace App\System\BasicInterface;


interface QueueRedisInterface
{
    /**
     * get a size by this key.
     * @param string $key
     * @return mixed
     */
    public function size(string $key) ;

    /**
     * get an index by this key
     * @param string $key
     * @param int $index
     * @return mixed
     */
    public function index(string $key,int $index = 0) ;

    /**
     * push a object.
     * @param string $key
     * @param string|int|array $data
     * @return mixed
     */
    public function push(string $key,$data) ;

    /**
     * last push a object.
     * @param string $key
     * @param $data
     * @return mixed
     */
    public function first(string $key,$data) ;


    /**
     * pop a value by key /ASC.
     * @param string $key
     * @return mixed
     */
    public function pop(string $key);

    /**
     * In top of the stack
     * @param string $key
     * @return mixed
     */
    public function shift(string $key);

    /**
     *
     * @return mixed
     */
    public function connect() ;

    /**
     * @return mixed
     */
    public function disconnect() ;
}