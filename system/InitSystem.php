<?php

/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/18 0018
 * Time: 09:50
 */

namespace App\System;

use App\System\Basic\Compact;
use App\System\BasicInterface\BootInterface;
use App\System\Config\SystemConfig;
use App\System\Database\db_mysqli;
use App\System\Handler\CurlHandler;
use App\System\Http\CurlProvider;
use App\System\Http\RequestProvider;
use App\System\Http\ResponseProvider;
use App\System\Http\RouteProvider;
use App\System\Other\ImageProvider;
use App\System\Other\UploadFileProvider;
use App\System\Other\VerifyProvider;
use App\System\Store\CacheProvider;
use App\System\Store\CookieProvider;
use App\System\Store\FileProvider;
use App\System\Store\QueueProvider;
use App\System\Store\SessionProvider;
use App\System\Template\TemplateProvider;
use App\System\Utils\TextUtils;

class InitSystem extends Compact implements BootInterface
{

    protected $router = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $basePath
     * @return $this
     */
    public function boot(string $basePath)
    {
        // TODO: Implement boot() method.
        static::setBasePath($basePath);
        $this->register();

        return $this;
    }

    public function register()
    {
        // TODO: Implement config() method.

        /**
         * The app system configuration.
         */
        app(SystemConfig::class)->boot(static::getBasePath());

        /**
         * The app database initialize.
         */
        if (conf("db","default") == "mysql"){
            $dbCfg = conf("db",conf("db","default"));
            $db = app(db_mysqli::class,$dbCfg['username'],$dbCfg['password'],$dbCfg['database'],$dbCfg['host'],$dbCfg['charset']);

        }

        /**
         * The app cookie initialize.
         *
         * CookieProvider::set("key","value");
         * CookieProvider::get("key");
         * CookieProvider::exist("key","value");
         * CookieProvider::remove("key");
         * CookieProvider::destroy();
         *
         */
        app(CookieProvider::class)->register();


        /**
         * The app session initialize.
         *
         * SessionProvider::set("key","val");
         * SessionProvider::get("key");
         * SessionProvider::exist("key");
         * SessionProvider::remove("key");
         * SessionProvider::destroy();
         *
         */
        app(SessionProvider::class)->register();

        /**
         * The app cache initialize.
         *
         * CacheProvider::set("test4","test redis");
         * CacheProvider::exist("test4");
         * CacheProvider::get("test4");
         * CacheProvider::remove("test4");
         * CacheProvider::destroy();
         */
        app(CacheProvider::class)->register();


        /**
         * Router.
         */
        $this->router = app(RouteProvider::class)->register();

        /**
         * All the request will be in this class instance & filter(Sql injection).
         * And if the Controller initialize,The program will be set request Object in __construct(RequestProvider $request) for the params.
         * All the Controller must be with this rule.
         *
         * Controller will extend the CompactController,more look that code.
         *
         */
        app(RequestProvider::class,app()->getInstance(RouteProvider::class)->getId())->register();

        /**
         * Response.
         * $response = app(ResponseProvider::class);
         * $response->setCookie(Cookie $cookie);
         * $response->setHeader(string $key,string $val);
         * $response->setHeaders(array $headers);[][key=>value]
         * $response->send($data,$code,ResponseProvider::RESPONSE_TYPE_JSON);
         *
         */
        app(ResponseProvider::class)->register();

        /**
         * template.
         *
         * example:
         * include file: {include file="header.html"}
         * output variable: {$__APP__}
         * output function: {function name="getTime"}
         * locale configuration: {lang value="getTime"}
         * config configuration: {conf p1="app" p2="appName"}
         * Array foreach : {foreach from="$data" item="item" key="key"}{$key} - {$item}{/foreach}
         * logic : {if $key eq '...'}something code...{else}something code...{/if}
         *
         */
        app(TemplateProvider::class)->register();

        /**
         * verify code.
         *
         * example:
         * output the verify image.
         * $this->getVerifyProvider()->entry();
         *
         * Verification the verify code,if is right return true else return false.
         * $this->getVerifyProvider()->check($verifyCode);
         */
        app(VerifyProvider::class)->register();

        /**
         * Queue.
         *
         * example:
         * $queue->push("ids",10,5);
         * $queue->push("ids",11);
         * $queue->push("ids",["a","b","c"]);
         * $queue->index("ids");
         * $queue->size("ids");
         * $queue->shift("ids");
         * $queue->pop("ids");
         *
         */
        app(QueueProvider::class)->register();


        /**
         * $file = app(FileProvider::class);
         * $file->save(ROOT_PATH."1.log","test".TIME_UTC.PHP_EOL);
         * $file->put(ROOT_PATH."1.log","test2".TIME_UTC.PHP_EOL);
         * $file->exist(ROOT_PATH."1.log");
         * $file->remove(ROOT_PATH."1.log");
         */
        app(FileProvider::class)->register();

        /**
         * image provider.
         * method:
         *      makeThumb
         *      makeCrop
         *      makeWater
         */
        app(ImageProvider::class)->register();

        /**
         * image upload file provider.
         *
         * example:
         * does not crop:
         * $upload = app()->getInstance(UploadFileProvider::class);
         * $res = $upload->initialize($image,"test")->save();
         *
         * dose crop the image:
         * $upload = app()->getInstance(UploadFileProvider::class);
         * $res = $upload
         *              ->setCropX(60)
         *              ->setCropY(60)
         *              ->setCropWidth(860)
         *              ->setCropHeight(456)
         *              ->initialize($image,"test")
         *              ->save();
         * return:
         * array(size = 8)
         * 'responseCode' => int 1
         * 'responseError' => string 'success.' (length=13)
         * 'size' => int 42
         * 'cropX' => int 60
         * 'cropY' => int 60
         * 'cropW' => int 860
         * 'cropH' => int 456
         * 'url' => string '/public/attachment/images/20170527/e9fef9c64cc51c5af079e3936694cea5.jpg' (length=71)
         *
         */
        app(UploadFileProvider::class)->register();

        /**
         * example:
         * $curl = app(CurlProvider::class);
         * $res = $curl->get("http://test.com/1.php");
         * $res = $curl->post("http://test.com/1.php");
         *
         * var_dump(json_decode($res,true));
         */
        app(CurlProvider::class)->register();



        return $this;
    }


    public function run()
    {
        // TODO: Implement run() method.

        //控制器初始化
        $filer = self::getBasePath() . $this->getRouter()->getControllerFilePath();
        if (file_exists($filer) ){
            $class = $this->getRouter()->getController();
            if (class_exists($class)) {
                $obj = app($class,app(RequestProvider::class));

                $action = $this->getRouter()->getAction();

                switch (TextUtils::upper($action)) {
                    case 'GET':
                        $obj->getAction($this->getRouter()->getId());
                        break;
                    case 'POST':
                        $obj->saveAction();
                        break;
                    case 'PUT':
                        $obj->putAction($this->getRouter()->getId());
                        break;
                    case 'DELETE':
                        $obj->removeAction($this->getRouter()->getId());
                        break;
                    default:
                        $obj->$action($this->getRouter()->getId());
                        break;
                }
            } else {
                output(array('response_code' => 0, 'response_err' => "Class access denied!"), ResponseProvider::HTTP_NO_CONTENT);
            }
        } else {
            output(array('response_code' => 0, 'response_err' => "File access denied!"), ResponseProvider::HTTP_NOT_FOUND);
        }

    }


    public function __destruct()
    {
        // TODO: Implement __destruct() method.

    }

    /**
     * @return RouteProvider
     */
    public function getRouter():RouteProvider
    {
        return $this->router;
    }


}