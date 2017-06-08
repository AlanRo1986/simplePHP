<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/18 0018
 * Time: 14:11
 */
namespace App\System\BasicInterface;

interface BootInterface{
    public function boot(string $basePath);
    public function register();
    public function run();
    public function __destruct();

}