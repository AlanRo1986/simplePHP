<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/24 0024
 * Time: 10:33
 */

namespace App\System\BasicInterface;


use App\System\Utils\TextUtils;

trait Router
{
    protected $id = 0;
    protected $controllerFile = "";
    protected $controller = "Init";
    protected $action = "get";
    protected $method = "get";
    protected $versionName = "#1";
    protected $versionCode = 1;
    protected $app = "app";

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return TextUtils::ucfirst($this->getApp())."\\Controller\\".$this->controller;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return int
     */
    public function getVersionCode(): int
    {
        return $this->versionCode;
    }

    /**
     * @return string
     */
    public function getVersionName(): string
    {
        return $this->versionName;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action)
    {
        $this->action = $action;
    }

    /**
     * @param string $controller
     */
    public function setController(string $controller)
    {
        $this->controller = TextUtils::ucfirst($controller);
    }

    /**
     * @param string $controllerFile
     */
    public function setControllerFile(string $controllerFile)
    {
        $this->controllerFile = TextUtils::ucfirst($controllerFile);
    }

    /**
     * @return string
     */
    public function getControllerFile(): string
    {
        return $this->controllerFile;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     * @param int $versionCode
     */
    public function setVersionCode(int $versionCode)
    {
        $this->versionCode = $versionCode;
    }

    /**
     * @param int $ver
     */
    public function setVersionName(int $ver)
    {
        $this->versionName = "version#".$ver;
    }

    /**
     * @param string $appType
     */
    public function setApp(string $appType)
    {
        $this->app = $appType;
    }

    /**
     * @return string
     */
    public function getApp(): string
    {
        return $this->app;
    }

    /**
     * @return string
     */
    public function getControllerFilePath(): string
    {
        return TextUtils::lower($this->getApp()."/".$this->getControlExtra()."/".$this->getVersionName()."/").$this->getControllerFile();
    }


}