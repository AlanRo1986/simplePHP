<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/23 0023
 * Time: 16:51
 *
 * example:
 * DateTimeUtils::getFormatDate();
 * DateTimeUtils::getFormatDateQuote();
 * DateTimeUtils::getFormatTime();
 * DateTimeUtils::getFormatTimeQuote();
 * DateTimeUtils::getWeekOfEn();
 * DateTimeUtils::getWeekOfCn();
 * DateTimeUtils::getWeekN();
 * DateTimeUtils::getDayOfYear();
 * DateTimeUtils::getDayOfMonth();
 * DateTimeUtils::getYear();
 * DateTimeUtils::getMonth();
 * DateTimeUtils::getDay();
 * DateTimeUtils::getHour();
 * DateTimeUtils::getMinute();
 * DateTimeUtils::getSecond();
 *
 */

namespace App\System\Utils;


use App\System\Basic\CompactUtils;

class DateTimeUtils extends CompactUtils
{
    const DATETIME_FULL_QUOTE = "Y-m-d H:i:s";
    const DATETIME_DATE_QUOTE = "Y-m-d";
    const DATETIME_TIME_QUOTE = "H:i:s";

    const DATETIME_FULL = "YmdHis";
    const DATETIME_DATE = "Ymd";
    const DATETIME_TIME = "His";

    const DATETIME_WEEK_L = "l";
    const DATETIME_WEEK_N = "N";
    const DATETIME_WEEK_W = "W";

    protected static $weeks = [
        1 => '星期一',
        2 => '星期二',
        3 => '星期三',
        4 => '星期四',
        5 => '星期五',
        6 => '星期六',
        7 => '星期日',
    ];

    /**
     * @return int
     */
    public static function getTime(){
        return time() - date('Z');
    }

    /**
     * @param int $time
     * @param string $format
     * @return string
     */
    public static function formatTime(int $time = 0,string $format = self::DATETIME_FULL_QUOTE):string {
        static::checkTime($time);
        return date($format, $time + date('Z'));
    }

    /**
     * @param string $strTime
     * @return int
     */
    public static function strToTime(string $strTime = ""):int {
        if(TextUtils::isEmpty($strTime)){
            return self::getTime();
        }

        $time = strtotime($strTime);

        return $time > (int)date('Z') ? $time - (int)date('Z') : $time;
    }

    /**
     * @param int $time
     * @param string $format
     * @return string
     */
    public static function getFormatDate(int $time = 0,string $format = self::DATETIME_DATE):string {
        static::checkTime($time);
        return self::formatTime($time,$format);
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getFormatDateQuote(int $time = 0):string {
        return self::getFormatDate($time,self::DATETIME_DATE_QUOTE);
    }

    /**
     * @param int $time
     * @param string $format
     * @return string
     */
    public static function getFormatTime(int $time = 0,string $format = self::DATETIME_TIME):string {
        static::checkTime($time);
        return self::formatTime($time,$format);
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getFormatTimeQuote(int $time = 0):string {
        return self::getFormatTime($time,self::DATETIME_TIME_QUOTE);
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getWeekOfEn(int $time = 0):string {
        static::checkTime($time);
        return self::formatTime($time,self::DATETIME_WEEK_L);
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getWeekN(int $time = 0):string {
        static::checkTime($time);
        return self::formatTime($time,self::DATETIME_WEEK_N);
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getWeekOfCn(int $time = 0):string {
        static::checkTime($time);
        return static::$weeks[self::formatTime($time,self::DATETIME_WEEK_N)];
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getWeekOfYear(int $time = 0):string {
        static::checkTime($time);
        return self::formatTime($time,self::DATETIME_WEEK_W);
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getDayOfYear(int $time = 0){
        static::checkTime($time);
        return self::formatTime($time,"z");
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getDayOfMonth(int $time = 0){
        static::checkTime($time);
        return self::formatTime($time,"d");
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getYear(int $time = 0){
        static::checkTime($time);
        return self::formatTime($time,"Y");
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getMonth(int $time = 0) {
        static::checkTime($time);
        return self::formatTime($time,"m");
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getDay(int $time = 0) {
        static::checkTime($time);
        return self::formatTime($time,"d");
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getHour(int $time = 0){
        static::checkTime($time);
        return self::formatTime($time,"H");
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getMinute(int $time = 0){
        static::checkTime($time);
        return self::formatTime($time,"i");
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getSecond(int $time = 0){
        static::checkTime($time);
        return self::formatTime($time,"s");
    }

    /**
     * @param int $time
     */
    protected static function checkTime(int &$time){
        if ($time <= 0){
            $time = self::getTime();
        }
    }

}