<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/16 0016
 * Time: 16:52
 */

use App\System\Basic\Application;

define("APP",true);
define("ROOT_PATH",str_replace("\\","/",__DIR__)."/");

if (PHP_VERSION < 7){
    die("The php version must greater or equal 7.0");
}

require "./system/helper/Helper.php";
require "./system/basic/Application.php";
require "./system/AutoLoader.php";

$app = Application::newInstance(ROOT_PATH);
$app->autoLoader();
$app->run();