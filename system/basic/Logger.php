<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/05/18 0018
 * Time: 15:37
 */

namespace App\System\Basic;

use App\System\BasicInterface\LoggerInterface;

abstract class Logger implements LoggerInterface
{
    const TypeLogger = [
        'Info' => 0,
        'Error' => 1,
        'Warn' => 2,
        'Debug' => 3,
    ];

    const TypeLogWrite = [
        'ERR' => "ERR",
        'INFO' => "INFO",
        'DEBUG' => "DEBUG"
    ];


    static function logInfo($logStr, $logType = "Logger")
    {
        // TODO: Implement logInfo() method.
        self::log($logStr,$logType,self::TypeLogger['Info']);
    }

    static function logError($logStr, $logType = "Logger")
    {
        // TODO: Implement logError() method.
        self::log($logStr,$logType,self::TypeLogger['Error']);

    }

    static function logWarn($logStr, $logType = "Logger")
    {
        // TODO: Implement logWarn() method.
        self::log($logStr,$logType,self::TypeLogger['Warn']);
    }

    static function logDebug($logStr, $logType = "Logger")
    {
        // TODO: Implement logDebug() method.
        self::log($logStr,$logType,self::TypeLogger['Debug']);
    }

    static function logWrite(string $query,string $level){

    }


    private static function log($logStr,string $logType,int $level){
        if ($logType == "Logger"){
            $logType = get_called_class();
        }

        $log = $logType.":\n";
        switch ($level){
            case self::TypeLogger['Debug']:
                break;
            case self::TypeLogger['Warn']:
                break;
            case self::TypeLogger['Error']:
                break;
            default:
                break;
        }

        echo PHP_EOL;
        echo $log;
        print_r($logStr);
        echo PHP_EOL;


    }




}

