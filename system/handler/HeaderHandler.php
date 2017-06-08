<?php

/**
 *
 * $header = new Header();
 * $header->set(Header::Headers_Key_CacheControl,"max-age=5400,s-maxage=5400");
 * $header->set(Header::Headers_Key_Age,3600);
 * $header->set(Header::Headers_Key_AcceptRanges,"bytes");
 * $header->set(Header::Headers_Key_KeepAlive,"keep-alive");
 *
 * $header->setCookies($cookie);
 * $header->setCookies(Cookie::newInstance()->setPath("/")->setKey("userName")->setValue("Alan")->setExpire(3600));
 *
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/5/24 0024
 * Time: 18:44
 */

namespace App\System\Handler;

use App\System\BasicInterface\AggregateInterface;
use App\System\Data\Cookie;

class HeaderHandler implements AggregateInterface
{

    const Headers_Key_Html = 'Connection';
    const Headers_Key_ContentType = 'Content-Type';
    const Headers_Key_ContentLength = 'Content-Length';
    const Headers_Key_Date = 'Date';
    const Headers_Key_Expires = 'Expires';
    const Headers_Key_KeepAlive = 'Keep-Alive';
    const Headers_Key_Server = 'Server';
    const Headers_Key_TransferEncoding = 'Transfer-Encoding';
    const Headers_Key_Age = 'age';
    const Headers_Key_AcceptRanges = 'accept-ranges';
    const Headers_Key_CacheControl = 'Cache-Control';
    const Headers_Key_ContentEncoding = 'Content-encoding';
    const Headers_Key_Set_Cookie = 'Set-Cookie';
    const Headers_Key_Set_ETag = 'ETag';
    const Headers_Key_Set_Last_Modified = 'Last-Modified';
    const Headers_Key_Author = 'Author';
    const Headers_Key_XPoweredBy = 'X-Powered-By';


    const Headers_Value_Html = 'text/html;';
    const Headers_Value_ImageIcon = 'image/x-icon;';
    const Headers_Value_ImagePng = 'image/png;';
    const Headers_Value_ImageJpeg = 'image/jpeg;';
    const Headers_Value_ImageGif = 'image/gif;';
    const Headers_Value_ImageBmp = 'image/bmp;';
    const Headers_Value_XJavascript = 'application/x-javascript;';
    const Headers_Value_Javascript = 'application/javascript;';
    const Headers_Value_Css = 'text/css;';
    const Headers_Value_OctetStream = 'application/octet-stream;';
    const Headers_Value_Excel = 'application/x-xls;';
    const Headers_Value_PPT = 'application/x-ppt;';
    const Headers_Value_Word = 'application/msword;';
    const Headers_Value_Charset = 'charset=UTF-8;';
    const Headers_Value_NoCache = 'no-cache';


    protected $headers = [];
    protected $cookies = [];


    public function __toString():string
    {
        // TODO: Implement __toString() method.

        return print_r($this);
    }

    public function setAll(array $headers){
        $this->headers = $headers;
        return $this;
    }

    /**
     * return all headers.
     * @return array
     */
    public function all()
    {
        // TODO: Implement all() method.

        return $this->headers;
    }

    /**
     * add a header by key & value.
     * @param string $key
     * @param $val
     */
    public function set(string $key, $val)
    {
        // TODO: Implement set() method.
        $this->headers[$key] = $val;
        return $this;
    }

    /**
     * get a header by key.
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        // TODO: Implement get() method.

        return $this->headers[$key];
    }

    /**
     * delete a header by key.
     * @param string $key
     */
    public function remove(string $key)
    {
        // TODO: Implement remove() method.

        unset($this->headers[$key]);
    }

    /**
     * clear all the headers.
     */
    public function destroy()
    {
        // TODO: Implement destroy() method.
        $this->headers = [];
    }

    /**
     * @param Cookie $cookie
     */
    public function setCookies(Cookie $cookie)
    {
        array_push($this->cookies,$cookie);
    }

    /**
     * @return array
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * return all the headers keys.
     * @return array
     */
    public function getKeys():array {
        return array_keys($this->all());
    }

    /**
     * return all the headers values.
     * @return array
     */
    public function getValues():array {
        return array_values($this->all());
    }

    /**
     * If is set the key,return true.
     * @param string $key
     * @return bool
     */
    public function exist(string $key):bool {
        return isset($this->all()[$key]);
    }

    /**
     * If is set the key,return true.
     * @param string $key
     * @return bool
     */
    public function has(string $key):bool
    {
        // TODO: Implement has() method.
        return $this->exist($key);
    }
}