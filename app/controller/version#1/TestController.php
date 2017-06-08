<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/26 0026
 * Time: 13:29
 */

namespace App\Controller;


use App\System\Basic\CompactController;
use App\System\BasicInterface\ControllerInterface;
use App\System\Http\RequestProvider;
use App\System\Other\ImageProvider;
use App\System\Other\UploadFileProvider;
use App\System\Store\FileProvider;
use App\System\Store\QueueProvider;
use App\System\Utils\ArrayUtils;

class TestController extends CompactController implements ControllerInterface
{
    public function __construct(RequestProvider $request)
    {
        parent::__construct($request);
    }

    /**
     * add action.
     * @return mixed
     */
    public function add()
    {
        // TODO: Implement add() method.
    }

    /**
     * edit action
     * @param int $id
     * @return mixed
     */
    public function edit(int $id)
    {
        // TODO: Implement edit() method.
    }

    /**
     * HTTP Method GET or get action.
     * @param int $id
     * @return mixed
     */
    public function getAction(int $id)
    {
        // TODO: Implement getAction() method.

        echo "<h1>test</h1>";


        //$image = app()->getInstance(FileProvider::class)->get(self::getBasePath()."1.data");

        //does not crop.
        //$upload = app()->getInstance(UploadFileProvider::class);
        //$res = $upload->initialize($image,"test")->save();

        //dose crop the image.
        //$upload = app()->getInstance(UploadFileProvider::class);
        /*$res = $upload
            ->setCropX(60)
            ->setCropY(60)
            ->setCropWidth(860)
            ->setCropHeight(456)
            ->initialize($image,"test")
            ->save();
        */
        /*
         * 'responseCode' => int 1
         * 'responseError' => string 'success.' (length=13)
         * 'size' => int 42
         * 'cropX' => int 60
         * 'cropY' => int 60
         * 'cropW' => int 860
         * 'cropH' => int 456
         * 'url' => string '/public/attachment/images/20170527/e9fef9c64cc51c5af079e3936694cea5.jpg' (length=71)
         *
         */
        //var_dump($res);

        //$image = app(ImageProvider::class);
        //$res = $image->makeWater("./1.jpg","./2.png",100,3);

//        $array = [
//            0 => ["id"=>10],
//            1 => ["id"=>9],
//            2 => ["id"=>13],
//            3 => ["id"=>12],
//            4 => ["id"=>19],
//        ];


//        var_dump(ArrayUtils::accessible($array));
//        var_dump(ArrayUtils::size($array));
//        var_dump(ArrayUtils::set($array,"test",["id"=>56]));
//        var_dump(ArrayUtils::exist($array,"test"));
//        var_dump(ArrayUtils::get($array,"test"));
//        var_dump(ArrayUtils::keys($array));
//        var_dump(ArrayUtils::values($array));
//        var_dump($array);
//        var_dump(ArrayUtils::remove($array,"test"));
//        var_dump($array);

    }

    /**
     * HTTP Method POST or save action.
     * Insert in db.
     * @return mixed
     */
    public function saveAction()
    {
        // TODO: Implement saveAction() method.
    }

    /**
     * HTTP Method PUT or update action.
     * Update in db.
     * @param int $id
     * @return mixed
     */
    public function putAction(int $id)
    {
        // TODO: Implement putAction() method.
    }

    /**
     * HTTP Method DELETE or delete action.
     * delete in db by id.
     * @param int $id
     * @return mixed
     */
    public function removeAction(int $id)
    {
        // TODO: Implement removeAction() method.
    }

    /**
     * HTTP Method DELETE or delete action.
     * delete in db by all.
     * @return mixed
     */
    public function destroy()
    {
        // TODO: Implement destroy() method.
    }
}