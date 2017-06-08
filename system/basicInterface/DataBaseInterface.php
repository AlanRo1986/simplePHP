<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/5/20 0020
 * Time: 14:53
 */

namespace App\System\BasicInterface;


interface DataBaseInterface
{
    public function getOne(string $sql,int $x = 0,int $y = 0):string;
    public function getRow(string $sql,string $output = "ARRAY_A",int $y = 0);
    public function getAll(string $sql,string $output = "ARRAY_A");
    public function getTables(string $output = "ARRAY_A"):array;
    public function getTableInfo(string $sql,string $output = "ARRAY_A"):string ;
    public function getInsertId():int ;
    public function autoExecute(string $table,array $fieldValues,string $mode = 'INSERT', string $where = ''):int;
    public function insertAll(string $table, array $fieldValues):int;


}