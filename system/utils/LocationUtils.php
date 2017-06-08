<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/23 0023
 * Time: 16:47
 *
 * $ip = app(LocationUtils::class);
 * $ip->getAddress()
 * $ip->getIP()
 *
 *
 */

namespace App\System\Utils;


use App\System\Basic\CompactUtils;

class LocationUtils extends CompactUtils
{
    protected $dataFile = "/";
    protected $fp = null;
    protected $first = "";
    protected $last = "";
    protected $total = -1;

    public function __construct()
    {
        $this->dataFile = app()->getRootPath()."system/utils/ip/ip.dat";


        $this->fp = fopen($this->dataFile,'rb');

        /**
         * 第一条ip索引的绝对偏移地址
         */
        $this->first = $this->get4b();

        /**
         * 最后一条ip索引的绝对偏移地址
         */
        $this->last = $this->get4b();

        /**
         * ip总数 索引区是定长的7个字节,在此要除以7.
         */
        $this->total =($this->last - $this->first) / 7 ;

    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        fclose($this->fp);
    }

    /**
     * 获取客户端ip地址
     * 如果你想要把ip记录到服务器上,请在写库时先检查一下ip的数据是否安全.
     * @return string
     */
    public static function getIP() {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset ($_SERVER ['REMOTE_ADDR']) && $_SERVER ['REMOTE_ADDR'] && strcasecmp($_SERVER ['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER ['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return trim($ip);
    }

    public function getAddressByGPS(float $lat,float $lnt){

    }

    public function getAddress(string $ip){
        /**
         * 获取ip 在索引区内的绝对编移地址
         */
        $ip_offset = $this->search($ip);
        $ipoffset = $ip_offset["offset"];
        $address["ip"] = $ip_offset["ip"];

        /**
         * 定位到索引区
         */
        fseek($this->fp,$ipoffset);

        /**
         * 索引区内的开始ip 地址
         */
        $address["startIp"] = long2ip($this->get4b());

        /**
         * 获取索引区内ip在ip记录区内的偏移地址
         */
        $address_offset = $this->getOffSet();

        /**
         * 定位到记录区内
         */
        fseek($this->fp,$address_offset);

        /**
         * 记录区内的结束ip 地址
         */
        $address["endIp"] = long2ip($this->get4b());

        /**
         * 读取标志字节
         */
        $flag = $this->getFlag();
        switch (ord($flag)) {
            case 1:  //地区1地区2都重定向
                $address_offset = $this->getOffSet();   //读取重定向地址
                fseek($this->fp,$address_offset);     //定位指针到重定向的地址
                $flag = $this->getFlag();               //读取标志字节

                switch (ord($flag)) {
                    case 2:  //地区1又一次重定向,
                        fseek($this->fp,$this->getOffSet());
                        $address["city"] = $this->getStr();
                        fseek($this->fp,$address_offset+4);      //跳4个字节
                        $address["area"] = $this->readAddress();  //地区2有可能重定向,有可能没有
                        break;
                    default: //地区1,地区2都没有重定向
                        fseek($this->fp,$address_offset);        //定位指针到重定向的地址
                        $address["city"] = $this->getStr();
                        $address["area"] = $this->readAddress();
                        break;
                }
                break;
            case 2: //地区1重定向 地区2没有重定向
                $address1_offset = $this->getOffSet();   //读取重定向地址
                fseek($this->fp,$address1_offset);
                $address["city"] = $this->getStr();
                fseek($this->fp,$address_offset+8);
                $address["area"] = $this->readAddress();
                break;
            default: //地区1地区2都没有重定向
                fseek($this->fp,$address_offset+4);
                $address["city"] = $this->getStr();
                $address["area"] = $this->readAddress();
                break;
        }
        //*过滤一些无用数据
        if (strpos($address["city"],"CZ88.NET")!=false){
            $address["city"]="未知";
        }
        if (strpos($address["area"],"CZ88.NET")!=false){
            $address["area"]=" ";
        }
        foreach($address as $k=>$item)
        {
            if(!TextUtils::isUtf8($address[$k]))
            {
                $address[$k] = iconv('gbk','utf-8',$item);
            }
        }
        return $address;
    }


    /**
     * @return mixed
     */
    protected function get4b(){
        $str = @unpack("V",fread($this->fp,4));
        return $str[1];
    }

    /**
     * 读取重定向了的偏移地址
     * @return mixed
     */
    protected function getOffSet(){
        $str = @unpack("V",fread($this->fp,3).chr(0));
        return $str[1];
    }

    /**
     * 读取ip的详细地址信息
     * @return string
     */
    protected function getStr(){
        $split = fread($this->fp,1);
        $str = "";
        while (ord($split)!=0) {
            $str .= $split;
            $split = fread($this->fp,1);
        }
        return $str;
    }

    /**
     * 将ip通过ip2long转成ipv4的互联网地址,再将他压缩成big-endian字节序
     * 用来和索引区内的ip地址做比较
     * @param $ip
     * @return string
     */
    protected function ipToInt(string $ip){
        return pack("N",intval(ip2long($ip)));
    }



    /**
     * 获取地址信息
     * @return string
     */
    protected function readAddress(){
        $now_offset = ftell($this->fp); //得到当前的指针位址
        $flag = $this->getFlag();
        switch (ord($flag)){
            case 0:
                $address = "";
                break;
            case 1:
            case 2:
                fseek($this->fp,$this->getOffSet());
                $address=$this->getStr();
                break;
            default:
                fseek($this->fp,$now_offset);
                $address=$this->getStr();
                break;
        }
        return $address;
    }


    /**
     * 获取标志1或2
     * 用来确定地址是否重定向了.
     * @return string
     */
    protected function getFlag(){
        return fread($this->fp,1);
    }

    /**
     * 用二分查找法在索引区内搜索ip
     * @param $ip
     * @return mixed
     */
    protected function search(string $ip){
        /**
         * 将域名转成ip
         */
        $ip = gethostbyname($ip);
        $ip_offset["ip"] = $ip;

        /**
         * 将ip转换成长整型
         */
        $ip = $this->ipToInt($ip);

        /**
         * 搜索的上边界
         */
        $firstIp = 0;

        /**
         * 搜索的下边界
         */
        $lastIp = $this->total;

        while ($firstIp <= $lastIp){
            /**
             * 计算近似中间记录 floor函数记算给定浮点数小的最大整数,说白了就是四舍五也舍
             */
            $i = floor(($firstIp + $lastIp) / 2);

            /**
             * 定位指针到中间记录
             */
            fseek($this->fp,$this->first + $i * 7);

            /**
             * 读取当前索引区内的开始ip地址,并将其little-endian的字节序转换成big-endian的字节序
             */
            $startIp = strrev(fread($this->fp,4));
            if ($ip < $startIp) {
                $lastIp = $i - 1;
            }
            else {
                fseek($this->fp,$this->getOffSet());
                $endIp = strrev(fread($this->fp,4));
                if ($ip > $endIp){
                    $firstIp = $i + 1;
                }
                else {
                    $ip_offset["offset"]=$this->first + $i * 7;
                    break;
                }
            }
        }
        return $ip_offset;
    }

}