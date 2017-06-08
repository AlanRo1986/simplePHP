<?php
/**
 *
 *
 * The Controller must be before of Action input.And the appType is optional input.
 * And ID must be before of version input.Because the program is check the id first.Then check the Version.
 * ID & Version must be type of int,The program will change it from string to int.
 * And Version final changed to 'Controller\version#1' or 'Controller\version#2'....
 *
 * example:
 * http://www.demo.com/Controller/Action/Id/Version/AppType(admin|web|api)
 * http://www.demo.com/Controller
 * http://www.demo.com/init/(Default:get)/(Default:0)/(Default:1)/(Default:web)
 * http://www.demo.com/api/init/1 action=>get(app:defaultRouteActionParam)
 * http://www.demo.com/api/init/get/1 action=>get:1
 * http://www.demo.com/?c=Controller&a=Action&ver=1
 * http://www.demo.com/?c=Controller&a=Action&ver=1&appType=admin //admin
 *
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/5/19 0019
 * Time: 21:22
 */

namespace App\System\Http;


use App\System\Basic\Provider;
use App\System\BasicInterface\Router;
use App\System\Utils\TextUtils;

class RouteProvider extends Provider
{
    use Router;

    protected $uri;
    protected $appType = [];
    protected $controlExtra = "Controller";
    protected $controlExtraFile = ".php";

    protected $key = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function middleware()
    {
        // TODO: Implement middleware() method.

        $this->setApp(conf("app","defaultAppType"));
        $this->setController(conf("app","defaultRouteController").$this->getControlExtra());
        $this->setControllerFile(conf("app","defaultRouteController").$this->getControlExtra().$this->getControlExtraFile());

        $this->setAction(conf("app","defaultRouteActionParam"));

        $this->setVersionCode(conf("app","defaultControllerVersion"));
        $this->setVersionName($this->getVersionCode());

        $this->setMethod($_SERVER['REQUEST_METHOD']);

        $this->setAppType(conf("app","appType"));

    }

    public function register()
    {
        // TODO: Implement register() method.
        $this->key['ctl'] = conf("app","defaultRouteControllerParam");
        $this->key['act'] = conf("app","defaultRouteActionParam");
        $this->key['type'] = conf("app","defaultRouteAppTypeParam");
        $this->key['id'] = conf("app","defaultRouteIdParam");
        $this->key['ver'] = conf("app","defaultRouteVersionParam");
        $this->resolver();

        return $this;
    }

    protected function resolver(){
        $this->setUri(parse_url($_SERVER['REQUEST_URI']));

        $uri = $this->getPathInfoUri();
        if (empty($uri) && count($uri) == 0){
            $uri = $this->getQueryUri();
        }


        if (isset($uri[$this->key['ctl']])){
            $this->setController($uri[$this->key['ctl']].$this->getControlExtra());
            $this->setControllerFile($uri[$this->key['ctl']].$this->getControlExtra().$this->getControlExtraFile());
        }

        if (isset($uri[$this->key['act']])){
            $this->setAction($uri[$this->key['act']]);
        }else{
            $this->setAction($this->getMethod());
        }

        if (isset($uri[$this->key['type']])){
            $this->setApp($uri[$this->key['type']]);
        }

        if (isset($uri[$this->key['id']])){
            $this->setId($uri[$this->key['id']]);
        }

        if (isset($uri[$this->key['ver']])){
            $this->setVersionCode($uri[$this->key['ver']]);
            $this->setVersionName($uri[$this->key['ver']]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getPathInfoUri():array {
        $path = $this->getUri()['path'];
        $path = explode("/",substr($path,1));

        $arr = [];

        foreach ($path as $k => $v){
            if (!empty($v)){
                if ((int)$v > 0){
                    $path[$k] = (int)$v;
                }

                if (is_string($path[$k]) && !isset($arr[$this->key['ctl']]) && !in_array($path[$k],$this->getAppType())){
                    $arr[$this->key['ctl']] = $path[$k];

                }elseif (is_string($path[$k]) && !in_array($path[$k],$this->getAppType()) && !isset($arr[$this->key['act']])){
                    $arr[$this->key['act']] = $path[$k];

                }elseif (is_string($path[$k]) && in_array($path[$k],$this->getAppType())){
                    $arr[$this->key['type']] = $path[$k];

                }elseif (is_int($path[$k]) && !isset($arr[$this->key['id']])){
                    $arr[$this->key['id']] = $path[$k];

                }elseif (is_int($path[$k]) && isset($arr[$this->key['ver']])){
                    $arr[$this->key['ver']] = $path[$k];

                }
            }

        }

        return $arr;
    }

    public function getQueryUri():array {

        $arr = array();
        if(isset($_REQUEST[$this->key['ctl']])){
            $arr[$this->key['ctl']] = $_REQUEST[$this->key['ctl']];
        }

        if(isset($_REQUEST[$this->key['act']])){
            $arr[$this->key['act']] = $_REQUEST[$this->key['act']];
        }

        if(isset($_REQUEST[$this->key['type']])){
            $arr[$this->key['type']] = $_REQUEST[$this->key['type']];
        }

        if(isset($_REQUEST[$this->key['id']])){
            $arr[$this->key['id']] = (int)$_REQUEST[$this->key['id']];
        }

        if(isset($_REQUEST[$this->key['ver']])){
            $arr[$this->key['ver']] = (int)$_REQUEST[$this->key['ver']];
        }
        return $arr;
    }

    /**
     * @param mixed $uri
     */
    public function setUri(array $uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param array $appType
     */
    public function setAppType(array $appType)
    {
        $this->appType = $appType;
    }

    /**
     * @return array
     */
    public function getAppType(): array
    {
        return $this->appType;
    }

    /**
     * @return string
     */
    public function getControlExtra(): string
    {
        return $this->controlExtra;
    }

    /**
     * @return string
     */
    public function getControlExtraFile(): string
    {
        return $this->controlExtraFile;
    }





}