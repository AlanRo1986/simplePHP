<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/05/25 0025
 * Time: 12:31
 */

namespace App\System\BasicInterface;


use App\System\Http\RequestProvider;

interface ControllerInterface
{

    /**
     * Initialize the class.
     * ControllerInterface constructor.
     * @param RequestProvider $request
     */
    public function __construct(RequestProvider $request);

    /**
     * add action.
     * @return mixed
     */
    public function add();

    /**
     * edit action
     * @param int $id
     * @return mixed
     */
    public function edit(int $id);

    /**
     * HTTP Method GET or get action.
     * @param int $id
     * @return mixed
     */
    public function getAction(int $id);

    /**
     * HTTP Method POST or save action.
     * Insert in db.
     * @return mixed
     */
    public function saveAction();

    /**
     * HTTP Method PUT or update action.
     * Update in db.
     * @param int $id
     * @return mixed
     */
    public function putAction(int $id);

    /**
     * HTTP Method DELETE or delete action.
     * delete in db by id.
     * @param int $id
     * @return mixed
     */
    public function removeAction(int $id);

    /**
     * HTTP Method DELETE or delete action.
     * delete in db by all.
     * @return mixed
     */
    public function destroy();


}