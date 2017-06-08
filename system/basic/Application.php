<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/18 0018
 * Time: 17:31
 */
declare(strict_types = 1);


/**
 * NameSpace expound:
 * The NameSpace rule is App\DirectoryName(UcFirst)\FileName
 * The App will changed ROOT_PATH
 * The DirectoryName will changed ucfirst to lcfirst;
 * The FileName is unaltered;
 *
 * example: App\System\InitSystem -> d:/www/system/InitSystem.php
 *          App\System\BaseInterFace\Compact -> d:/www/system\baseInterFace\Compact.php
 *
 */
namespace App\System\Basic;

use App\System\AutoLoader;
use App\System\Database\db_mysqli;
use App\System\InitSystem;
use App\System\Utils\TextUtils;

class Application
{
    protected static $rootPath = "";
    protected static $app = null;
    protected static $instances = [];

    protected static $configs = [];
    protected static $locales = [];
    protected static $begin_time = 0;

    public function __construct(string $basePath)
    {
        static::$begin_time = microtime(true);
        static::$rootPath = $basePath;
    }

    public static function newInstance(string $basePath):Application{
        if (static::$app == null){
            static::$app = new Application($basePath);
        }
        return static::$app;
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.

    }


    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if (conf("app","isDebug") == true){
            echo "<hr><p style='color: red'>";
            print_r(sprintf("The PHP version %s.\tMYSQL version %s.\tNew instances %s.",PHP_VERSION,$this->getInstance(db_mysqli::class)->getVersion(),count($this->getInstances())));
            print_r("The APP run time:".sprintf("%1\$.4f",(microtime(true) - static::$begin_time))."s");
            echo "</p>";
        }

    }

    /**
     * auto loader class files.
     */
    public function autoLoader()
    {
        AutoLoader::newInstance($this->getRootPath())->run();
    }

    /**
     * run the app.
     */
    public function run()
    {
        $this->make(InitSystem::class)->boot($this->getRootPath())->run();
    }

    /**
     * remove the instance
     * @param $abstract
     */
    public function dropInstance(string $abstract){
        unset(static::$instances[$abstract]);
    }

    public function getInstances(){
        return static::$instances;
    }

    /**
     * 实例化对象
     * $this->make(InitSystem::class,[param1,param2....]);
     * @param $abstract
     * @param $args
     * @return mixed|object
     */
    public function make(string $abstract = null,array $args = []){

        $this->dropInstance($abstract);

        $reflector = new \ReflectionClass($abstract);

        //throw new Exception.
        if ($reflector->isInstantiable() == false){
            return $this->notInstantiable($abstract);
        }

        //get the class constructor method.
        $constructor = $reflector->getConstructor();

        if ($constructor == null){
            return $this->build($abstract);
        }

        return $this->buildInstanceArgs($abstract,$reflector,$args);
    }


    /**
     * get root path.
     * @return string
     */
    public function getRootPath(): string
    {
        return static::$rootPath;
    }

    /**
     * throw has not this method.
     * @param string $name
     * @param string $class
     */
    public function invalidMethod(string $name,string $class){
        die(sprintf("Invalid method [%s] in [%s].",$name,$class));
    }

    /**
     * app()->getConfigs("app","appName")
     * app()->getConfigs("db")
     * @param array $configs
     */
    public function setConfigs(array $configs)
    {
        static::$configs = $configs;
    }

    /**
     * @param array $locales
     */
    public function setLocales(array $locales)
    {
        $arr = [];
        foreach ($locales as $k => $v){
            $arr[TextUtils::upper($k)] = $v;
        }
        static::$locales = $arr;
    }


    /**
     * get the config key.
     * @param array ...$args
     * if $args is empty return arrays,else return string.
     * @return array|string
     */
    public function getConfigs(...$args)
    {
        //[db][name][host]
        if (empty($args)){
            return static::$configs;
        }
        $keys = null;
        foreach ($args as $arg){
            if ($keys == null){
                $keys = static::$configs[$arg];
            }else{
                if (TextUtils::isEmpty($arg) == false){
                    $keys = isset($keys[$arg]) ? $keys[$arg] : TextUtils::upper($arg);
                }
            }
        }
        return $keys;
    }


    /**
     * get the locale key.
     * if have not found,return upper(key).
     * example:
     *      app()->getLocales();
     *      app()->getLocales("app");
     * @param string $key
     * @return array|string
     */
    public function getLocales(string $key = null)
    {
        if (is_null($key)){
            return static::$locales;
        }

        $key = TextUtils::upper($key);
        if (isset(static::$locales[$key])){
            return static::$locales[$key];
        }
        return $key;
    }

    /**
     * get locale language.
     * @return string
     */
    public function getLocaleString():string {
        $ref = "cn";
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) == false){
            return $ref;
        }

        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4);

        if (preg_match("/en/i", $lang))
            $ref = "en";
        else if (preg_match("/fr/i", $lang))
            $ref = "fr";
        else if (preg_match("/de/i", $lang))
            $ref = "de";
        else if (preg_match("/jp/i", $lang))
            $ref = "jp";
        else if (preg_match("/ko/i", $lang))
            $ref = "ko";
        else if (preg_match("/es/i", $lang))
            $ref = "es";
        else if (preg_match("/sv/i", $lang))
            $ref = "sv";

        return $ref;
    }

    /**
     * build the class.
     * @param $abstract
     * @return mixed
     */
    protected function build(string $abstract)
    {
        $key = $this->replaceInstanceKey($abstract);

        if ($this->isInstance($key) == true){
            return static::$instances[$key];
        }

        return static::$instances[$key] = new $abstract();
    }

    /**
     * build and instance the abstract
     * @param string $abstract
     * @param $instance
     * @param $args
     * @return mixed
     */
    protected function buildInstanceArgs(string $abstract,$instance,array $args = [])
    {
        $key = $this->replaceInstanceKey($abstract);

        if ($this->isInstance($key) == true){
            return static::$instances[$key];
        }

        return static::$instances[$key] = $instance->newInstanceArgs($args);
    }

    /**
     * get has instanced object.
     * app()->getInstance(db_mysqli::class);
     * @param string $abstract
     * @return mixed
     */
    public function getInstance(string $abstract){
        if (strpos($abstract,"\\") > 0){
            $abstract = $this->replaceInstanceKey($abstract);
        }

        return static::$instances[$abstract];
    }

    /**
     * if instanced the abstract
     * @param string $abstract
     * @return bool
     */
    protected function isInstance(string $abstract):bool {
        return isset(static::$instances[$abstract]);
    }

    /**
     * format instances keys(\) quote.
     * @param string $str
     * @return mixed
     */
    protected function replaceInstanceKey(string $str){
        return str_replace("\\","_",$str);
    }

    /**
     * can not Instantiable the class.
     * @param string $abstract
     * @throws \Exception
     */
    protected function notInstantiable(string $abstract)
    {
        throw new \Exception("Target [$abstract] is not Instantiable.");
    }

}