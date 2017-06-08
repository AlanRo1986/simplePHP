<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/5/20 0020
 * Time: 14:53
 */

namespace App\System\Basic;

use App\System\BasicInterface\DataBaseInterface;
use App\System\Utils\TextUtils;

abstract class DbCore extends Compact implements DataBaseInterface {

    const OBJECT = "OBJECT";
    const ARRAY_A = "ARRAY_A";
    const ARRAY_N = "ARRAY_N";

    protected $show_errors      = IS_DEBUG;
    protected $num_queries      = 0;
    protected $conn_queries     = 0;
    protected $last_query       = null;
    protected $last_error       = null;
    protected $col_info         = null;
    protected $captured_errors  = array();
    protected $cache_dir        = false;
    protected $cache_queries    = false;
    protected $cache_inserts    = false;
    protected $use_disk_cache   = false;
    protected $cache_timeout    = 1; // hours
    protected $timers           = array();
    protected $total_query_time = 0;
    protected $db_connect_time  = 0;
    protected $sql_log_file     = false;
    protected $do_profile       = false;
    protected $profile_times    = array();
    protected $insert_id    = 0;
    protected $from_disk_cache = false;

    protected $num_rows;
    protected $last_result;
    protected $func_call;
    protected $dbh;


    public function __construct()
    {
        parent::__construct();

        $this->cache_dir = conf("storage","cacheDb");

        //对数据要求高的网站不建议开启缓存
        //$this->use_disk_cache = true;
        //$this->cache_queries = true;
        //$this->cache_timeout = 0.2; //单位是小时
    }

    /**
     * @param string $host
     * @param int $default
     * @return array
     */
    protected function get_host_port(string $host, int $default = 3306 ):array
    {
        $port = $default;
        if ( false !== strpos( $host, ':' ) ) {
            list( $host, $port ) = explode( ':', $host );
            $port = (int) $port;
        }
        return array( $host, $port );
    }

    /**
     * @param $err_str
     */
    protected function register_error($err_str)
    {
        // Keep track of last error
        $this->last_error = $err_str;

        // Capture all errors to an error array no matter what happens
        $this->captured_errors[] = array
        (
            'error_str' => $err_str,
            'query'     => $this->last_query
        );
    }

    /**
     * enable errors.
     */
    public function show_errors()
    {
        $this->show_errors = true;
    }

    /**
     * disable errors.
     */
    public function hide_errors()
    {
        $this->show_errors = false;
    }

    /**
     *
     */
    public function flush()
    {
        // Get rid of these
        $this->last_result = null;
        $this->col_info = null;
        $this->last_query = null;
        $this->from_disk_cache = false;
    }

    /**
     * $db->getOne("select userName from ".DB_PREFIX."user where id = 3")
     * @param string $sql
     * @param int $x
     * @param int $y
     * @return string
     */
    public function getOne(string $sql,int $x = 0,int $y = 0):string
    {
        // TODO: Implement getOne() method.

        // Log how the function was called
        $this->func_call = "\$db->getOne(\"$sql\",$x,$y)";

        // If there is a query then perform it if not then use cached results..
        if (TextUtils::isEmpty($sql) == false) {
            $this->query($sql);
        }

        // Extract var out of cached results based x,y vals
        if ( $this->last_result[$y] )
        {
            $values = array_values(get_object_vars($this->last_result[$y]));
        }

        // If there is a value return it else return null
        return (isset($values[$x]) && $values[$x]!=='') ? $values[$x] : "";
    }

    /**
     * $db->getRow("select * from ".DB_PREFIX."user where id = 2")
     * @param string $sql
     * @param string $output
     * @param int $y
     * @return array
     */
    public function getRow(string $sql,string $output = "ARRAY_A",int $y = 0) {
        // TODO: Implement getRow() method.

        // Log how the function was called
        $this->func_call = "\$db->getRow(\"$sql\",$output,$y)";

        // If there is a query then perform it if not then use cached results..
        if ( $sql )
        {
            $this->query($sql);
        }

        // If the output is an object then return object using the row offset..
        if ( $output == self::OBJECT )
        {
            return $this->last_result[$y]?$this->last_result[$y]:null;
        }
        // If the output is an associative array then return row as such..
        elseif ( $output == self::ARRAY_A )
        {
            return $this->last_result[$y]?get_object_vars($this->last_result[$y]):null;
        }
        // If the output is an numerical array then return row as such..
        elseif ( $output == self::ARRAY_N )
        {
            return $this->last_result[$y]?array_values(get_object_vars($this->last_result[$y])):null;
        }
        // If invalid output type was specified..
        else
        {
            $this->show_errors ? trigger_error(" \$db->getRow(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N",E_USER_WARNING) : null;
        }

    }

    /**
     * @param string $query
     * @param int $x
     * @return array
     */
    public function getCol(string $query,int $x = 0):array
    {

        $new_array = array();

        // If there is a query then perform it if not then use cached results..
        if ( $query )
        {
            $this->query($query);
        }

        // Extract the column values
        $j = count($this->last_result);
        for ( $i=0; $i < $j; $i++ )
        {
            $new_array[$i] = $this->getOne("",$x,$i);
        }

        return $new_array;
    }

    /**
     * $db->getAll("select * from ".DB_PREFIX."user where id > 4")
     * @param string $query
     * @param string $output
     * @return array
     */
    public function getAll(string $query,string $output = "ARRAY_A")
    {
        // TODO: Implement getAll() method.

        // Log how the function was called
        $this->func_call = "\$db->getAll(\"$query\", $output)";

        // If there is a query then perform it if not then use cached results..
        if ( $query )
        {
            $this->query($query);
        }

        // Send back array of objects. Each row is an object
        if ( $output == self::OBJECT )
        {
            return $this->last_result;
        }
        elseif ( $output == self::ARRAY_A || $output == self::ARRAY_N )
        {
            if ( $this->last_result )
            {
                $new_array = [];
                $i = 0;
                foreach( $this->last_result as $row )
                {

                    $new_array[$i] = get_object_vars($row);

                    if ( $output == self::ARRAY_N )
                    {
                        $new_array[$i] = array_values($new_array[$i]);
                    }

                    $i++;
                }

                return $new_array;
            }

        }
        return false;
    }

    /**
     * @param string $output
     * @return array
     */
    public function getTables(string $output = "ARRAY_A"):array
    {
        // TODO: Implement getTables() method.

        $sql    = 'SHOW TABLES ';

        $this->func_call = "\$db->getTables(\"$sql\", $output)";

        // If there is a query then perform it if not then use cached results..
        if ( $sql )
        {
            $this->query($sql);
        }

        // Send back array of objects. Each row is an object
        if ( $output == self::OBJECT )
        {
            return $this->last_result;
        }

        if ( $this->last_result )
        {
            $new_array = [];
            $i=0;
            foreach( $this->last_result as $row )
            {

                $arr = get_object_vars($row);
                $arr = array_values($arr);

                $new_array[$i] = $arr[0];
                $i++;
            }

            return $new_array;
        }
        else
        {
            return array();
        }
    }

    /**
     * @param string $sql
     * @param string $output
     * @return string
     */
    public function getTableInfo(string $sql, string $output = "ARRAY_A"):string
    {
        // TODO: Implement getTableInfo() method.

        $this->func_call = "\$db->getTableInfo(\"$sql\", $output)";

        // If there is a query then perform it if not then use cached results..
        if ( $sql )
        {
            $this->query($sql);
        }

        // Send back array of objects. Each row is an object
        if ( $output == self::OBJECT )
        {
            return $this->last_result;
        }

        if ( $this->last_result )
        {
            $str = "";
            foreach( $this->last_result as $row )
            {

                $arr = get_object_vars($row);
                $arr = array_values($arr);

                $str = $arr[1];
            }

            return $str;
        }
        else
        {
            return "";
        }

    }

    /**
     * return last inserted id
     * @return int
     */
    public function getInsertId():int
    {
        // TODO: Implement getInsertId() method.

        return $this->insert_id;
    }

    /**
     * $resId = $db->autoExecute("user",['userName' => 'a010','userPasswd' => md5("a010",'age' => mt_rand(19,40)]); insert
     * $resId = $db->autoExecute("user",['userPasswd' => md5("a010",'age' => mt_rand(19,40)],"UPDATE","id = 10"); update
     * auto insert data or update data
     * @param string $table 没有前缀的数据库表名
     * @param array $fieldValues 要插入的数据 key:val,key为字段名
     * @param string $mode 模式,INSERT跟UPDATE
     * @param string $where 如果模式为UPDATE必须传入这个条件
     * @return int
     */
    public function autoExecute(string $table, array $fieldValues, string $mode = 'INSERT', string $where = ''):int
    {
        // TODO: Implement autoExecute() method.
        $field_names = $this->getCol('DESC ' . DB_PREFIX.$table);

        $sql = '';
        if ($mode == 'INSERT') {
            $fields = $values = array ();
            foreach ($field_names AS $value) {
                if (@ array_key_exists($value, $fieldValues) == true) {
                    $fields[] = $value;
                    $fieldValues[$value] = stripslashes($fieldValues[$value]);
                    $values[] = "'" . addslashes($fieldValues[$value]) . "'";
                }
            }

            if (!empty ($fields)) {
                $sql = 'INSERT INTO ' . DB_PREFIX . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
            }
        } else {
            if (TextUtils::isEmpty($where)){
                return -1;
            }
            $sets = array ();
            foreach ($field_names AS $value) {
                if (array_key_exists($value, $fieldValues) == true) {
                    $fieldValues[$value] = stripslashes($fieldValues[$value]);
                    $sets[] = $value . " = '" . addslashes($fieldValues[$value]) . "'";
                }
            }

            if (!empty ($sets)) {
                $sql = 'UPDATE ' . DB_PREFIX.$table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
            }
        }

        if ($sql) {
            return $this->query($sql);
        } else {
            return -1;
        }


    }

    /**
     * insert more data.
     * example:
     *  $fieldValues = [0=>[],1=>[]]
     * @param string $table
     * @param array $fieldValues
     * @return int
     */
    public function insertAll(string $table, array $fieldValues):int
    {
        // TODO: Implement insertAll() method.
        if (count($fieldValues) <= 0) return -1;

        $fields = array_keys($fieldValues[0]);
        array_walk($fields);
        $values = array();
        foreach ($fieldValues as $data) {
            $value = array();
            foreach ($data as $val) {
                $val = addslashes(stripslashes($val));//重新加斜线，防止从数据库直接读取出错
                $val = "'".$val."'";

                if (is_scalar($val)){
                    $value[] = $val;
                }
            }
            $values[] = '('.implode(',',$value).')';
        }
        $sql = 'INSERT INTO `'.DB_PREFIX.$table.'` ('.implode(',',$fields).') VALUES '.implode(',',$values);
        $result = $this->query($sql);
        $insert_id = $this->getInsertId();


        return $insert_id ? $insert_id : $result;

    }

    /**
     * 取表结构信息名字
     * @param string $info_type
     * @param int $col_offset
     * @return array
     */
    public function get_col_info(string $info_type = "name",int $col_offset = -1):array
    {

        if ( $this->col_info )
        {
            if ( $col_offset == -1 )
            {
                $new_array = [];
                $i=0;
                foreach($this->col_info as $col )
                {
                    $new_array[$i] = $col->{$info_type};
                    $i++;
                }
                return $new_array;
            }
            else
            {
                return $this->col_info[$col_offset]->{$info_type};
            }

        }
        return [];
    }

    /**
     * save cache.
     * @param string $query
     * @param bool $is_insert
     */
    protected function store_cache(string $query,bool $is_insert)
    {
        // The would be cache file for this query
        $cache_file = $this->cache_dir.'/'.md5($query);

        // disk caching of queries
        if ( $this->use_disk_cache && ( $this->cache_queries && ! $is_insert ) || ( $this->cache_inserts && $is_insert ))
        {
            if ( ! is_dir($this->cache_dir) )
            {
                $this->register_error("Could not open cache dir: $this->cache_dir");
                $this->show_errors ? trigger_error("Could not open cache dir: $this->cache_dir",E_USER_WARNING) : null;
            }
            else
            {
                // Cache all result values
                $result_cache = array
                (
                    'col_info' => $this->col_info,
                    'last_result' => $this->last_result,
                    'num_rows' => $this->num_rows,
                    'return_value' => $this->num_rows,
                );
                file_put_contents($cache_file, serialize($result_cache));
                if( file_exists($cache_file . ".updating") )
                    @unlink($cache_file . ".updating");
            }
        }

    }

    /**
     * @param string $query
     * @return mixed
     */
    protected function get_cache(string $query){

        // The would be cache file for this query
        $cache_file = $this->cache_dir.'/'.md5($query);

        // Try to get previously cached version
        if ( $this->use_disk_cache && file_exists($cache_file) )
        {
            // Only use this cache file if less than 'cache_timeout' (hours)
            if ( (time() - filemtime($cache_file)) > ($this->cache_timeout * 3600) &&
                !(file_exists($cache_file . ".updating") && (time() - filemtime($cache_file . ".updating") < 60)) )
            {
                touch($cache_file . ".updating"); // Show that we in the process of updating the cache
            }
            else
            {
                $result_cache = unserialize(file_get_contents($cache_file));

                $this->col_info = $result_cache['col_info'];
                $this->last_result = $result_cache['last_result'];
                $this->num_rows = $result_cache['num_rows'];

                $this->from_disk_cache = true;

                return $result_cache['return_value'];
            }
        }
        return null;
    }

    /**
     * @return float
     */
    protected function timer_get_cur():float
    {
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * @param $timer_name
     */
    protected function timer_start($timer_name)
    {
        $this->timers[$timer_name] = $this->timer_get_cur();
    }

    /**
     * @param $timer_name
     * @return float
     */
    protected function timer_elapsed($timer_name):float
    {
        return round($this->timer_get_cur() - $this->timers[$timer_name],2);
    }

    /**
     * @param $timer_name
     */
    protected function timer_update_global($timer_name)
    {
        if ( $this->do_profile )
        {
            $this->profile_times[] = array
            (
                'query' => $this->last_query,
                'time' => $this->timer_elapsed($timer_name)
            );
        }

        $this->total_query_time += $this->timer_elapsed($timer_name);
    }


    /**
     * Creates a SET nvp sql string from an associative array (and escapes all values)
     *
     * Usage:
     *
     *     $db_data = array('login'=>'jv','email'=>'jv@vip.ie', 'user_id' => 1, 'created' => 'NOW()');
     *
     *     $db->query("INSERT INTO users SET ".$db->get_set($db_data));
     *
     *     ...OR...
     *
     *     $db->query("UPDATE users SET ".$db->get_set($db_data)." WHERE user_id = 1");
     *
     * Output:
     *
     *     login = 'jv', email = 'jv@vip.ie', user_id = 1, created = NOW()
     *
     * @param array $params
     * @return string|void
     */
    protected function get_set(array $params)
    {
        if( !is_array( $params ) )
        {
            $this->register_error( 'get_set() parameter invalid. Expected array in '.__FILE__.' on line '.__LINE__);
            return array();
        }
        $sql = array();
        foreach ( $params as $field => $val )
        {
            if ( $val === 'true' || $val === true )
                $val = 1;
            if ( $val === 'false' || $val === false )
                $val = 0;

            switch( $val ){
                case 'NOW()' :
                case 'NULL' :
                    $sql[] = "$field = $val";
                    break;
                default :
                    $sql[] = "$field = '".$this->escape( $val )."'";
            }
        }

        return implode( ', ' , $sql );
    }

    /**
     * Function for operating query count
     *
     * @param bool $all Set to false for function to return queries only during this connection
     * @param bool $increase Set to true to increase query count (internal usage)
     * @return int Returns query count base on $all
     */
    protected function count ($all = true, $increase = false) {
        if ($increase) {
            $this->num_queries++;
            $this->conn_queries++;
        }

        return ($all) ? $this->num_queries : $this->conn_queries;
    }

    /**
     * @param string $string
     * @return mixed
     */
    protected function real_escape_string(string $string){
        return $this->dbh->real_escape_string($string);
    }


    /**
     * @param string $sql
     * @return mixed
     */
    abstract protected function query(string $sql);
    abstract protected function escape(string $str);
    abstract public function getVersion();
}