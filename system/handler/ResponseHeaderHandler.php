<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/24 0024
 * Time: 16:38
 */

namespace App\System\Handler;

class ResponseHeaderHandler extends HeaderHandler
{

    /**
     * Constructor.
     *
     * @param array $headers An array of HTTP headers
     */
    public function __construct(array $headers = array())
    {
        if (!isset($this->headers[HeaderHandler::Headers_Key_CacheControl])) {
            $this->set(HeaderHandler::Headers_Key_CacheControl, '');
        }

        if (empty($headers) == false){
            $this->setAll($headers);
        }
    }

}
