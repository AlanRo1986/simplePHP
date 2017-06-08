<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/26 0026
 * Time: 10:47
 */

namespace App\System\BasicInterface;


interface QueueInterface
{

    /**
     * @param string $key
     * @param int $index
     * @return mixed
     */
    public function index(string $key,int $index = 0);

    /**
     * @param string $key
     * @return mixed
     */
    public function size(string $key) ;

    /**
     * @param string $key
     * @param $data
     * @param int $expire
     * @return mixed
     */
    public function push(string $key,$data,int $expire = 0);

    /**
     * @param string $key
     * @param $data
     * @param int $expire
     * @return mixed
     */
    public function first(string $key,$data,int $expire = 0);


    /**
     * @param string $key
     * @return mixed
     */
    public function pop(string $key);

    /**
     * @param string $key
     * @return mixed
     */
    public function shift(string $key);

    /**
     * @return mixed
     */
    public function getConnection();

    /**
     * @param $name
     * @return mixed
     */
    public function setConnection($name);

}