<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/27 0027
 * Time: 15:05
 */

namespace App\System\Basic;


abstract class CompactUtils
{
    public static function __callStatic($method, $parameters)
    {
        throw new \Exception("Method {$method} does not exist.");
    }

    public function __call($method, $parameters)
    {
        throw new \Exception("Method {$method} does not exist.");
    }

}