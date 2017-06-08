<?php

/**
 * example:
 * $cookie = Cookie::newInstance()
 * ->setDomain("www.test.com")
 * ->setExpire(3600*24)
 * ->setPath("/root/")
 * ->setKey("id")
 * ->setValue(10);
 *
 *
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/5/24 0024
 * Time: 18:34
 */

namespace App\System\Data;

class Cookie
{
    protected $key;
    protected $value;
    protected $expire;
    protected $path;
    protected $domain;

    public function __construct()
    {

        $this->setExpire((int)conf("cookies","cookieExpire"));
        $this->setPath(conf("cookies","cookiePath"));
        $this->setDomain(conf("cookies","cookieDomain"));
    }

    public static function newInstance():Cookie{
        return new Cookie();
    }

    public function __toString():string
    {
        // TODO: Implement __toString() method.

        return $this->getKey().":".$this->getValue();
    }

    /**
     * @return string
     */
    public function getKey():string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getValue():string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getExpire():string
    {
        return $this->expire;
    }

    /**
     * @return string
     */
    public function getDomain():string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getPath():string
    {
        return $this->path;
    }

    /**
     * @param string $domain
     * @return $this
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @param mixed $expire
     * @return $this
     */
    public function setExpire(int $expire)
    {
        $this->expire = $expire + TIME_UTC;
        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey(string $key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue(string $value)
    {
        $this->value = $value;
        return $this;
    }


}