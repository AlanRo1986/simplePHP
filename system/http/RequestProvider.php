<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/24 0024
 * Time: 10:21
 */

namespace App\System\Http;


use App\System\Basic\Provider;

class RequestProvider extends Provider
{
    const FilterData = [
        "xss" =>"[\\'\\\"\\;\\*\\<\\>].*\\bon[a-zA-Z]{3,15}[\\s\\r\\n\\v\\f]*\\=|\\b(?:expression)\\(|\\<script[\\s\\\\\\/]|\\<\\!\\[cdata\\[|\\b(?:eval|alert|prompt|msgbox)\\s*\\(|url\\((?:\\#|data|javascript)",
        "sql" => "[^\\{\\s]{1}(\\s|\\b)+(?:select\\b|update\\b|insert(?:(\\/\\*.*?\\*\\/)|(\\s)|(\\+))+into\\b).+?(?:from\\b|set\\b)|[^\\{\\s]{1}(\\s|\\b)+(?:create|delete|drop|truncate|rename|desc)(?:(\\/\\*.*?\\*\\/)|(\\s)|(\\+))+(?:table\\b|from\\b|database\\b)|into(?:(\\/\\*.*?\\*\\/)|\\s|\\+)+(?:dump|out)file\\b|\\bsleep\\([\\s]*[\\d]+[\\s]*\\)|benchmark\\(([^\\,]*)\\,([^\\,]*)\\)|(?:declare|set|select)\\b.*@|union\\b.*(?:select|all)\\b|(?:select|update|insert|create|delete|drop|grant|truncate|rename|exec|desc|from|table|database|set|where)\\b.*(charset|ascii|bin|char|uncompress|concat|concat_ws|conv|export_set|hex|instr|left|load_file|locate|mid|sub|substring|oct|reverse|right|unhex)\\(|(?:master\\.\\.sysdatabases|msysaccessobjects|msysqueries|sysmodules|mysql\\.db|sys\\.database_name|information_schema\\.|sysobjects|sp_makewebtask|xp_cmdshell|sp_oamethod|sp_addextendedproc|sp_oacreate|xp_regread|sys\\.dbms_export_extension)",
        'other' => "\\.\\.[\\\\\\/].*\\%00([^0-9a-fA-F]|$)|%00[\\'\\\"\\.]"
    ];

    protected $request = [];
    protected $data;
    protected $http_accept;
    protected $method;
    protected $id;

    public function __construct(int $id)
    {
        parent::__construct();

        $this->setId($id);
    }

    /**
     * The first run the middleware.
     */
    public function middleware()
    {
        // TODO: Implement middleware() method.

        $this->setHttpAccept('application/json');
        $this->setMethod($_SERVER['REQUEST_METHOD']);

    }

    /**
     * instance register.
     */
    public function register()
    {
        // TODO: Implement register() method.

        //通常的
        $arr = array_merge($_GET,$_POST);
        if (!empty($arr)) {
            $this->setRequest($arr);
        }

        //restful
        $data = @file_get_contents('php://input');
        if (!empty($data)) {
            if (json_decode($data, TRUE)){
                $this->setData(json_decode($data, TRUE));
            }else{
                $this->setData($this->stringToArray($data));
            }
        }

        if(!empty($this->getData())){
            $this->setRequest(array_merge($this->getRequest(), $this->getData()));
        }

        $this->filterRequest($this->getRequest());

    }

    /**
     * string changed to array.
     *  //id=14&token=ENezbCYvJvx0W9GrXMdbG0DDnyVgYANvQ9F7ifIzmOT19sJiVyaitZuY8Z5Uo6O&ajax=1
     * @param string $str
     * @return array
     */
    protected function stringToArray(string $str):array
    {
        $tp = explode("&",$str);
        if (empty($tp)){
            return [];
        }

        $arr = [];
        foreach ($tp as $v){
            $tp2 = explode("=",$v);
            if(count($tp2) == 2){
                $arr[$tp2[0]] = $tp2[1];
            }else{
                $arr[$tp2[0]] = str_replace($tp2[0]."=","",$v);
            }
        }
        return $arr;
    }

    /**
     * filter Sql injection.
     * @param array $data
     */
    protected function filterRequest(array $data)
    {
        $pattern = "/(select[\s])|(insert[\s])|(update[\s])|(delete[\s])|(from[\s])|(where[\s])|(<script[\s])|(eval[\s])/i";

        foreach ($data as $k => $v) {
            if (is_array($v) == false) {
                if (preg_match($pattern, $v, $match)) {
                    die ("SQL Injection denied!");
                }

                foreach (self::FilterData as $a => $b) {
                    if (preg_match("/" . $b . "/is", $v) == 1 || preg_match("/" . $b . "/is", urlencode($v)) == 1) {
                        die ("SQL Injection denied!");
                    }
                }

            } else {
                $this->filterRequest($v);
            }
        }
    }


    /**
     * @return mixed
     */
    public function getData()
    {
        return is_array($this->data) ? $this->data : [];
    }

    /**
     * @return mixed
     */
    public function getHttpAccept()
    {
        return $this->http_accept;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getRequest():array
    {
        return is_array($this->request) ? $this->request : [];
    }

    /**
     * @param mixed $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param mixed $http_accept
     */
    public function setHttpAccept(string $http_accept)
    {
        $this->http_accept = $http_accept;
    }

    /**
     * @param mixed $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     * @param mixed $arr
     */
    public function setRequest(array $arr)
    {
        $this->request = $arr;
    }


    /**
     * @return string
     */
    public function getHttpDomain():string{
        return getDomain();
    }

    /**
     * @return string
     */
    public function getHttp():string{
        return getHttp();
    }

}