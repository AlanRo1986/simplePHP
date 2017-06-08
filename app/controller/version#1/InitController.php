<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/24 0024
 * Time: 12:20
 */

namespace App\Controller;


use App\System\Basic\CompactController;
use App\System\BasicInterface\ControllerInterface;
use App\System\Data\ValidateData;
use App\System\Http\CurlProvider;
use App\System\Http\RequestProvider;
use App\System\Store\QueueProvider;
use App\System\Utils\DateTimeUtils;
use App\System\Utils\LocationUtils;
use App\System\Utils\ValidateUtils;
use App\System\Utils\WordsUtils;
use App\System\Utils\XmlUtils;


class InitController extends CompactController implements ControllerInterface
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

        //$this->outputCookie("test","testValue".TIME_UTC);
        //$this->output($_REQUEST);

        $curl = app(CurlProvider::class);
        $res = $curl->get("http://test.com/1.php");
        $res = $curl->post("http://test.com/1.php");

        var_dump(json_decode($res,true));

        $this->assign("title","Title".TIME_UTC);
        $this->assign("data",$this->getData());
        $this->display();

    }

    protected function getData():array {
        return [
            'Alan' => 'Ro.',
            'Lily' => 'Zu.',
            'Mark' => 'Pi.',
            'Lin' => 'Koo.'
        ];
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