<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/22 0022
 * Time: 10:58
 */

namespace App\System\Basic;


use App\System\BasicInterface\MiddlewareInterface;

abstract class Provider extends Compact implements MiddlewareInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * The first run the middleware.
     */
    abstract public function middleware();

    /**
     * instance register.
     */
    abstract public function register();

}