<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19 0019
 * Time: 22:20
 */

namespace App\System\Config;


use App\System\Basic\Compact;


class SystemConfig extends Compact
{
    const localeFile = "lang.php";

    /**
     * boot.
     * @param string $path
     */
    public function boot(string $path){
        static::setBasePath($path);

        self::defineConfigData();
        self::defineLocalesData();
        self::defineStorageDirectory();
        self::configuration();
    }

    /**
     * configuration the config data.
     */
    protected static function defineConfigData(){
        app()->setConfigs(require static::getBasePath().static::configPath."ConstantConfig.php");
    }

    /**
     * configuration the locales,default locale is cn.
     */
    protected static function defineLocalesData(){
        $defaultLocale = app()->getConfigs("app","locale");
        $targetLocale = app()->getLocaleString();

        $localePathApp = static::getBasePath()."app/lang/%s/".self::localeFile;
        $localePathAdmin = static::getBasePath()."admin/lang/%s/".self::localeFile;

        if (file_exists(sprintf($localePathApp,$targetLocale)) == false){
            $localePathApp = sprintf($localePathApp,$defaultLocale);
        }else{
            $localePathApp = sprintf($localePathApp,$targetLocale);
        }

        if (file_exists(sprintf($localePathAdmin,$targetLocale)) == false){
            $localePathAdmin = sprintf($localePathAdmin,$defaultLocale);
        }else{
            $localePathAdmin = sprintf($localePathAdmin,$targetLocale);
        }

        app()->setLocales(array_merge(require $localePathApp,require $localePathAdmin));
    }

    /**
     * configuration the storage directory,is invalid will make the directory.
     * the storage directory is defined in @system/config/ConstantConfig.php:storage
     *
     */
    protected static function defineStorageDirectory(){
        $storage = app()->getConfigs("storage");

        foreach ($storage as $v){
            if (file_exists(static::getBasePath().$v) == false){
                @mkdir(static::getBasePath().$v);
            }
        }
    }

    /**
     * system dynamic configuration.
     */
    protected static function configuration(){

        define('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? 1 : 0);
        if (IS_CGI) {
            $_temp = explode('.php', $_SERVER ["PHP_SELF"]); // /index.php
            define('_PHP_FILE_', rtrim(str_replace($_SERVER ["HTTP_HOST"], '', $_temp [0] . '.php'), '/'));
        } else {
            define('_PHP_FILE_', rtrim($_SERVER ["SCRIPT_NAME"], '/'));
        }

        if (app()->getConfigs("app","isDebug") == true) {
            ini_set("display_errors", true);
            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^E_DEPRECATED);
        } else {
            ini_set("display_errors", false);
            error_reporting(0);
        }

        ini_set("always_populate_raw_post_data",-1);

        date_default_timezone_set(app()->getConfigs("app","defaultTimeZone"));

        define("TIME_UTC", getTime());
        define("IS_DEBUG", conf("app","isDebug"));
        define("CLIENT_IP", getClientIp());
        define("SITE_DOMAIN", getDomain());
        define("DB_PREFIX", conf("db",conf("db","default"))["prefix"]);
        define("CACHE", conf("cache",conf("cache","default")));
        define("SESSION", conf("session",""));
        define("COOKIE", conf("cookies",""));
        //define("APP", conf("app",""));

    }




}