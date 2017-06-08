<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/24 0024
 * Time: 15:29
 */

namespace App\System\Basic;


use App\System\Data\Cookie;
use App\System\Http\RequestProvider;

abstract class CompactController extends Compact
{
    protected $request;

    public function __construct(RequestProvider $request)
    {
        parent::__construct();

        $this->setRequestProvider($request);
    }

    protected function display(string $fileName = "init"){
        display($fileName.".html");
    }

    protected function assign(string $key,$value){
        assign($key,$value);
    }

    /**
     * @param array $data
     * @param string $err
     * @param int $code
     */
    protected function output(array $data,string $err = "",int $code = 0){
        $data['responseError'] = $err;
        $data['responseCode'] = $code;
        output($data);
    }

    /**
     * @param string $key
     * @param string $val
     */
    protected function outputCookie(string $key,string $val){
        outputCookie(Cookie::newInstance()->setKey($key)->setValue($val));
    }

    /**
     * @param string $key
     * @param string $val
     */
    protected function outputHeader(string $key,string $val){
        outputHeader($key,$val);
    }

    /**
     * @param array $headers
     */
    protected function outputHeaders(array $headers){
        outputHeaders($headers);
    }

    /**
     * @param RequestProvider $request
     */
    protected function setRequestProvider(RequestProvider $request)
    {
        $this->request = $request;
    }

    /**
     * @return RequestProvider
     */
    protected function getRequestProvider():RequestProvider
    {
        return $this->request;
    }

    /**
     * @return array
     */
    protected function getRequest():array {
        return $this->getRequestProvider()->getRequest();
    }


}