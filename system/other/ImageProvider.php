<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/23 0023
 * Time: 16:46
 */

namespace App\System\Other;


use App\System\Basic\Provider;

class ImageProvider extends Provider
{

    const WATER_POSITION_NONE = 0;
    const WATER_POSITION_LEFT_TOP = 1;
    const WATER_POSITION_RIGHT_TOP = 2;
    const WATER_POSITION_LEFT_BOTTOM = 3;
    const WATER_POSITION_RIGHT_BOTTOM = 4;
    const WATER_POSITION_CENTER = 5;



    /**
     * @var string
     */
    protected $dir = "";

    /**
     * @var string
     */
    protected $prefix = "";

    /**
     * 图片裁剪的X坐标
     * @var int
     */
    protected $cropX = 0;

    /**
     * 图片裁剪的Y坐标
     * @var int
     */
    protected $cropY = 0;

    /**
     * 图片裁剪的宽度
     * @var int
     */
    protected $cropWidth = 0;

    /**
     * 图片裁剪的高度
     * @var int
     */
    protected $cropHeight = 0;

    /**
     * The first run the middleware.
     */
    public function middleware()
    {
        // TODO: Implement middleware() method.
    }

    /**
     * instance register.
     */
    public function register()
    {
        // TODO: Implement register() method.

    }


    /**
     * 创建缩略图
     * @param string $source_path 原文件路径
     * @param string $filename 不带.jpg的纯文件名
     * @param array $thumb_width 缩略图宽度数组：[360,1024]
     * @return array
     * @throws \Exception
     */
    public function makeThumb(string $source_path = '',string $filename = '' ,array $thumb_width = [360,1024])
    {
        if (empty($thumb_width) || empty($source_path) || empty($filename)){
            throw new \Exception("The params must be input.",5);
        }

        $info = $this->getImageInfo($source_path);
        $sourceWidth  = $info[0];
        $sourceHeight = $info[1];
        $mime  = $info['mime'];

        /**
         * 原图比例
         */
        $source_ratio  = $sourceHeight / $sourceWidth;

        /**
         * 新图比例
         */
        $target_ratio = 1;

        /**
         * get the image file ext.
         */
        $ext = $this->getImageExt($mime);

        /**
         * get type of image file.
         */
        $fucType = $this->getImageFuncType($mime);

        /**
         * create image object of type.
         */
        $source_image = $this->createImageByType($source_path,$mime);

        foreach ($thumb_width as $k => $v){
            if($v > $sourceWidth){
                $v = $sourceWidth;
            }

            /**
             * 源图过高
             */
            if ($source_ratio > $target_ratio){
                $cropped_height  = $v;
                $cropped_width = $v / $source_ratio;
            }
            /**
             * 源图过宽
             */
            elseif ($source_ratio < $target_ratio){
                $cropped_height  = $v * $source_ratio;
                $cropped_width = $v;
            }
            /**
             * 源图适中
             */
            else{
                $cropped_width  = $v;
                $cropped_height = $v;
            }

            /**
             * 创建一副新图层
             */
            $target_image  = @imagecreatetruecolor($cropped_width, $cropped_height);

            /**
             * 白色背景
             */
            $color = @imagecolorallocate($target_image,255,255,255);
            @imagefill($target_image,0,0,$color);

            /**
             * 缩放图片
             */
            @imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $cropped_width, $cropped_height, $sourceWidth, $sourceHeight);

            //写到文件
            $imageFunc = 'image' . $fucType;
            @$imageFunc($target_image, $this->dir.$this->prefix.$filename."_".$thumb_width[$k].".".$ext);
            @imagedestroy($target_image);
        }

        @imagedestroy($source_image);

        $res['w'] = $sourceWidth;
        $res['h'] = $sourceHeight;
        return $res;

    }

    /**
     * 原图裁剪
     * @param string $source_path
     * @return bool
     * @throws \Exception
     */
    public function makeCrop(string $source_path)
    {
        if ($this->isCrop() == false){
            throw new \Exception("Must be set cropX & cropY & cropWidth & cropHeight first of Crop image.",7);
        }
        if (empty($source_path)){
            throw new \Exception("The params must be input.",5);
        }

        $info = $this->getImageInfo($source_path);
        $sourceWidth  = $info[0];
        $sourceHeight = $info[1];
        $mime   = $info['mime'];

        /**
         * get type of image file.
         */
        $fucType = $this->getImageFuncType($mime);

        /**
         * create image object of type.
         */
        $sourceImage = $this->createImageByType($source_path,$mime);

        /**
         * Create a crop image.
         */
        $cropImage = imagecreatetruecolor($this->getCropWidth(), $this->getCropHeight());

        /**
         * corp the image.
         */
        imagecopy($cropImage, $sourceImage, 0, 0, $this->getCropX(), $this->getCropY(), $sourceWidth, $sourceHeight);

        $imageFunc = 'image' . $fucType;
        $imageFunc($cropImage, $source_path );

        imagedestroy($sourceImage);
        imagedestroy($cropImage);


        return true;
    }

    /**
     *
     * example:
     * $image = app(ImageProvider::class);
     * $res = $image->makeWater("./1.jpg","./2.png",4);
     *
     * @param string $source
     * @param string $water
     * @param int $position
     * @return bool
     */
    public function makeWater(string $source = "",string $water = "",int $position = 4)
    {
        if(empty($source) || empty($water)){
            return false;
        }

        if(!file_exists($source) || !file_exists($water)){
            return false;
        }

        //图片信息
        $sInfo = $this->getImageInfo($source);
        $wInfo = $this->getImageInfo($water);

        //如果图片小于水印图片，不生成图片
        if($sInfo["0"] < $wInfo["0"] || $sInfo['1'] < $wInfo['1']){
            return false;
        }

        $sImage = $this->createImageByType($source,$sInfo['mime']);
        $wImage = $this->createImageByType($water,$wInfo['mime']);

        $sImageFunc = $this->getImageFuncType($sInfo['mime']);

        //设定图像的混色模式
        imagealphablending($wImage, true);
        switch (intval($position))
        {
            case self::WATER_POSITION_NONE:
                break;
            case self::WATER_POSITION_LEFT_TOP: // left top
                $posY = $posX = 10;
                break;

            case self::WATER_POSITION_RIGHT_TOP: // right top
                $posY = 10;
                $posX = $sInfo[0] - $wInfo[0] - 10;
                break;

            case self::WATER_POSITION_LEFT_BOTTOM: // left bottom
                $posY = $sInfo[1] - $wInfo[1] - 10;
                $posX = 10;
                break;

            case self::WATER_POSITION_RIGHT_BOTTOM: // right bottom
                $posY = $sInfo[1] - $wInfo[1] - 10;
                $posX = $sInfo[0] - $wInfo[0] - 10;
                break;

            case self::WATER_POSITION_CENTER: // center
                $posY = $sInfo[1] / 2 - $wInfo[1] / 2;
                $posX = $sInfo[0] / 2 - $wInfo[0] / 2;

                break;
        }

        if ($position > 0 && $position < 6){
            /**
             * 创建一副新图层
             */
            $cut  = @imagecreatetruecolor($sInfo[0], $sInfo[1]);

            imagecopy($cut,$sImage,0,0,0,0,$sInfo[0], $sInfo[1]);

            imagecopy($cut,$wImage,$posX,$posY,0,0,$wInfo[0], $wInfo[1]);

            imagecopyresampled ($sImage, $cut, 0, 0, 0, 0 , $sInfo[0], $sInfo[1], $sInfo[0], $sInfo[1] );

        }

        /**
         * save image
         */
        $sImageFunc = 'image' . $sImageFunc;
        $sImageFunc($sImage,$source,100);

        imagedestroy($sImage);
        imagedestroy($wImage);

        return true;
    }


    /**
     * @param string $imageFile
     * @return mixed
     * @throws \Exception
     */
    public function getImageInfo(string $imageFile) {
        $info = getimagesize($imageFile);
        if ($info == false){
            throw new \Exception("The image get information invalid.",6);
        }
        return $info;
    }

    /**
     * @param string $imageFile
     * @param string $mime
     * @return resource
     * @throws \Exception
     */
    public function createImageByType(string $imageFile,string $mime){
        switch ($mime){
            case 'image/gif':
                $image = @imagecreatefromgif($imageFile);
                break;
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($imageFile);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($imageFile);
                break;
            default:
                throw new \Exception("Upload file of type must be jpeg/png/gif.",2);
                break;
        }
        return $image;
    }

    /**
     * @param string $mime
     * @return string
     * @throws \Exception
     */
    protected function getImageFuncType(string $mime){
        switch ($mime){
            case 'image/gif':
                $fucType = 'gif';
                break;

            case 'image/jpeg':
                $fucType = "jpeg";
                break;

            case 'image/png':
                $fucType = 'png';
                break;

            default:
                throw new \Exception("Upload file of type must be jpeg/png/gif.",2);
                break;
        }

        return $fucType;
    }

    /**
     * @param string $mime
     * @return string
     * @throws \Exception
     */
    protected function getImageExt(string $mime){
        switch ($mime){
            case 'image/gif':
                $ext = 'gif';
                break;

            case 'image/jpeg':
                $ext = "jpg";
                break;

            case 'image/png':
                $ext = 'png';
                break;

            default:
                throw new \Exception("Upload file of type must be jpeg/png/gif.",2);
                break;
        }

        return $ext;
    }

    /**
     * @param string $dir
     * @return $this
     */
    public function setDir(string $dir)
    {
        $this->dir = $dir;
        return $this;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @param int $cropHeight
     * @return $this
     */
    public function setCropHeight(int $cropHeight)
    {
        $this->cropHeight = $cropHeight;
        return $this;
    }

    /**
     * @param int $cropWidth
     * @return $this
     */
    public function setCropWidth(int $cropWidth)
    {
        $this->cropWidth = $cropWidth;
        return $this;
    }

    /**
     * @param int $cropX
     * @return $this
     */
    public function setCropX(int $cropX)
    {
        $this->cropX = $cropX;
        return $this;
    }

    /**
     * @param int $cropY
     * @return $this
     */
    public function setCropY(int $cropY)
    {
        $this->cropY = $cropY;
        return $this;
    }


    /**
     * @return string
     */
    protected function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    protected function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @return int
     */
    protected function getCropY(): int
    {
        return $this->cropY;
    }

    /**
     * @return int
     */
    protected function getCropX(): int
    {
        return $this->cropX;
    }

    /**
     * @return int
     */
    protected function getCropHeight(): int
    {
        return $this->cropHeight;
    }

    /**
     * @return int
     */
    protected function getCropWidth(): int
    {
        return $this->cropWidth;
    }

    /**
     * @return bool
     */
    protected function isCrop():bool {
        return $this->getCropWidth() > 0 && $this->getCropHeight() > 0;
    }

}