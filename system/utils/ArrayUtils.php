<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/23 0023
 * Time: 16:46
 */

namespace App\System\Utils;


use App\System\Basic\CompactUtils;
use ArrayAccess;

class ArrayUtils extends CompactUtils
{

    /**
     * @param $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * @param $arr
     * @return bool
     */
    public static function isArray($arr){
        return is_array($arr);
    }

    /**
     * @param array $arr
     * @return int
     */
    public static function size(array $arr){
        return count($arr);
    }

    /**
     * @param array $array
     * @param $key
     * @param $val
     * @return array
     */
    public static function set(array &$array,$key,$val){
        if (self::isArray($array)){
            $array[$key] = $val;
        }else{
            $array = [$key => $val];
        }
        return $array;
    }

    /**
     * @param array $array
     * @param $key
     * @return mixed
     */
    public static function get(array $array, $key)
    {
        if (self::isArray($array)) {
            return $array[$key];
        }
        return $key;
    }

    /**
     * @param array $array
     * @return array
     */
    public static function keys(array $array){
        return array_keys($array);
    }

    /**
     * @param array $array
     * @return array
     */
    public static function values(array $array){
        return array_values($array);
    }

    /**
     * @param array $array
     * @param $key
     * @return bool
     */
    public static function remove(array &$array,$key){
        unset($array[$key]);
        return true;
    }

    /**
     * @param array $array
     * @param $key
     * @return bool
     */
    public static function exist(array $array,$key){
        return isset($array[$key]);
    }

    /**
     * example:
     * $array = [
     *          0 => ["id"=>10],
     *          1 => ["id"=>9],
     *          2 => ["id"=>13],
     *          3 => ["id"=>12],
     *          4 => ["id"=>19],
     *      ];
     * ArrayUtils::sortDesc($array,"id");
     *
     * @param array $array
     * @param $key
     * @return bool
     */
    public static function sortDesc(array &$array,$key){
        if (!self::isArray($array)){
            return false;
        }

        $left = null;
        for ($i = 0;$i < count($array) - 1;$i++){
            for ($j = $i + 1;$j < count($array);$j++){
                if ($array[$i][$key] < $array[$j][$key]){
                    $left = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $left;
                }
            }
        }

    }

    /**
     *
     * example:
     * $array = [
     *          0 => ["id"=>10],
     *          1 => ["id"=>9],
     *          2 => ["id"=>13],
     *          3 => ["id"=>12],
     *          4 => ["id"=>19],
     *      ];
     * ArrayUtils::sortAsc($array,"id");
     *
     * @param array $array
     * @param $key
     * @return bool
     */
    public static function sortAsc(array &$array,$key){
        if (!self::isArray($array)){
            return false;
        }

        $left = null;
        for ($i = 0;$i < count($array) - 1;$i++){
            for ($j = $i + 1;$j < count($array);$j++){
                if ($array[$i][$key] > $array[$j][$key]){
                    $left = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $left;
                }
            }
        }
    }

    /**
     * example:
     * $array = [
     *          0 => ["id"=>10],
     *          1 => ["id"=>9],
     *          2 => ["id"=>13],
     *          3 => ["id"=>12],
     *          4 => ["id"=>19],
     *      ];
     * ArrayUtils::sortByQuick($array,"id",0,4);
     *
     * @param array $array
     * @param $key
     * @param int $start
     * @param int $end
     * @return bool
     */
    public static function sortByQuick(array &$array,$key,int $start = 0,int $end){
        if (self::isArray($array) == false){
            return false;
        }

        if ($start < $end){
            $left = $array[$start];
            $tmp = null;
            $i = $start;$j = $end;

            do{
                while ($array[$i][$key] < $left[$key] && $i < $end){
                    $i++;
                }
                while ($array[$j][$key] > $left[$key] && $j > $start){
                    $j--;
                }
                if ($i <= $j){
                    $tmp = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $tmp;
                    $i++;
                    $j--;
                }
            }while($i <= $j);

            if ($start < $j){
                static::sortByQuick($array,$key,$start,$j);
            }

            if ($end > $i){
                static::sortByQuick($array,$key,$i,$end);
            }

        }

    }

    /**
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    public static function merge(array $arr1,array $arr2){
        return array_merge($arr1,$arr2);
    }

    /**
     * Is Array Assoc
     *
     * @access public
     * @param  $array
     *
     * @return boolean
     */
    public static function is_array_assoc($array)
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Is Array Multidim
     *
     * @access public
     * @param  $array
     *
     * @return boolean
     */
    public static function is_array_multidim($array)
    {
        if (!is_array($array)) {
            return false;
        }

        return (bool)count(array_filter($array, 'is_array'));
    }

    /**
     * Array Flatten Multidim
     *
     * @access public
     * @param  $array
     * @param  $prefix
     *
     * @return array
     */
    public static function array_flatten_multidim($array, $prefix = false)
    {
        $return = array();
        if (is_array($array) || is_object($array)) {
            if (empty($array)) {
                $return[$prefix] = '';
            } else {
                foreach ($array as $key => $value) {
                    if (is_scalar($value)) {
                        if ($prefix) {
                            $return[$prefix . '[' . $key . ']'] = $value;
                        } else {
                            $return[$key] = $value;
                        }
                    } else {
                        if ($value instanceof \CURLFile) {
                            $return[$key] = $value;
                        } else {
                            $return = array_merge(
                                $return,
                                self::array_flatten_multidim(
                                    $value,
                                    $prefix ? $prefix . '[' . $key . ']' : $key
                                )
                            );
                        }
                    }
                }
            }
        } elseif ($array === null) {
            $return[$prefix] = $array;
        }
        return $return;
    }



}