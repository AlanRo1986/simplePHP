<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24 0024
 * Time: 19:08
 */

namespace App\System\BasicInterface;

interface AggregateInterface
{
    public function all();
    public function exist(string $key):bool ;
    public function set(string $key,$val);
    public function get(string $key);
    public function remove(string $key);
    public function has(string $key):bool ;
    public function destroy();
}