<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/18 0018
 * Time: 15:38
 */

namespace App\System\BasicInterface;


interface LoggerInterface
{
    static function logInfo($logStr,$logType = "Logger");
    static function logError($logStr,$logType = "Logger");
    static function logWarn($logStr ,$logType = "Logger");
    static function logDebug($logStr,$logType = "Logger");
}