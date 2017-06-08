<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/18 0018
 * Time: 14:47
 */

namespace App\System\Basic;

class AutoloaderDispatcher
{


    /**
     * 返回自动加载类的集合对象
     * @return array
     */
    public static function getClass():array{
        return require __DIR__."/../config/AutoloaderClass.php";
    }

    public static function getInterface():array{
        return require __DIR__."/../config/AutoloaderInterface.php";
    }

}