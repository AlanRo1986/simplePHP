<?php

namespace App\System\Database;

use App\System\Basic\DbCore;

class db_mysqli extends DbCore
{

    const mysqli_str_err = [
        1 => 'Require $dbuser and $dbpassword to connect to a database server',
        2 => 'Error establishing mySQLi database connection. Correct user/password? Correct hostname? Database server running?',
        3 => 'Require $dbname to select a database',
        4 => 'mySQLi database connection is not active',
        5 => 'Unexpected error while trying to select database'
    ];

    protected $dbuser = false;
    protected $dbpassword = false;
    protected $dbname = false;
    protected $dbhost = false;
    protected $dbport = false;
    protected $encoding = false;
    protected $rows_affected = false;
    protected $result;


    public function __construct(string $user = '', string $password = '', string $name = '', string $host = 'localhost', string $encoding = 'utf8')
    {
        parent::__construct();

        $this->dbuser = $user;
        $this->dbpassword = $password;
        $this->dbname = $name;
        list( $this->dbhost, $this->dbport ) = $this->get_host_port( $host, 3306 );
        $this->encoding = $encoding;
    }

    protected function quick_connect(string $user = '',string $password = '',string $name = '',string $host = 'localhost',string $port = '3306', string $encoding = 'utf8'):bool
    {
        $return_val = false;
        if ( ! $this->connect($user, $password, $host, $port) ) ;
        else if ( ! $this->select($name,$encoding) ) ;
        else $return_val = true;
        return $return_val;
    }

    protected function connect(string $user = '',string $password = '', string $host = 'localhost', $port = false):bool {
        $return_val = false;

        // Keep track of how long the DB takes to connect
        $this->timer_start('db_connect_time');

        // If port not specified (new connection issued), get it
        if( ! $port ) {
            list( $host, $port ) = $this->get_host_port( $host, 3306 );
        }

        // Must have a user and a password
        if ( !$user )
        {
            $this->register_error(self::mysqli_str_err[1].' in '.__FILE__.' on line '.__LINE__);
            $this->show_errors ? trigger_error(self::mysqli_str_err[1],E_USER_WARNING) : null;
        }
        // Try to establish the server database handle
        else
        {
            $this->dbh = new \mysqli($host,$user,$password, '', $port);
            // Check for connection problem
            if( $this->dbh->connect_errno )
            {
                $this->register_error(self::mysqli_str_err[2].' in '.__FILE__.' on line '.__LINE__);
                $this->show_errors ? trigger_error(self::mysqli_str_err[2],E_USER_WARNING) : null;
            }
            else
            {
                $this->dbuser = $user;
                $this->dbpassword = $password;
                $this->dbhost = $host;
                $this->dbport = $port;
                $return_val = true;

                $this->conn_queries = 0;
            }
        }

        return $return_val;
    }


    protected function select(string $name = '', string $encoding = ''):bool {

        $return_val = false;
        // Must have a database name
        if ( ! $name )
        {
            $this->register_error(self::mysqli_str_err[3].' in '.__FILE__.' on line '.__LINE__);
            $this->show_errors ? trigger_error(self::mysqli_str_err[3],E_USER_WARNING) : null;
        }

        // Must have an active database connection
        else if ( ! $this->dbh )
        {
            $this->register_error(self::mysqli_str_err[4].' in '.__FILE__.' on line '.__LINE__);
            $this->show_errors ? trigger_error(self::mysqli_str_err[4],E_USER_WARNING) : null;
        }


        // Try to connect to the database
        else if ( !@$this->dbh->select_db($name) )
        {
            // Try to get error supplied by mysql if not use our own
            if ( !$str = @$this->dbh->error)
                  $str = self::mysqli_str_err[5];

            $this->register_error($str.' in '.__FILE__.' on line '.__LINE__);
            $this->show_errors ? trigger_error($str,E_USER_WARNING) : null;
        }
        else
        {
            $this->dbname = $name;
            if($encoding!='')
            {
                $encoding = strtolower(str_replace("-","",$encoding));
                $charsets = array();
                $result = $this->dbh->query("SHOW CHARACTER SET");
                while($row = $result->fetch_array(MYSQLI_ASSOC))
                {
                    $charsets[] = $row["Charset"];
                }
                if(in_array($encoding,$charsets)){
                    $this->dbh->query("SET NAMES '".$encoding."'");
                }
            }

            $return_val = true;
        }

        return $return_val;
    }

    /**
     * Format a mySQL string correctly for safe mySQL insert
     * (no mater if magic quotes are on or not)
     * @param string $str
     * @return mixed
     */
    protected function escape(string $str)
    {
        // If there is no existing database connection then try to connect
        if ( ! isset($this->dbh) || ! $this->dbh )
        {
            $this->connect($this->dbuser, $this->dbpassword, $this->dbhost, $this->dbport);
            $this->select($this->dbname, $this->encoding);
        }

                    if ( get_magic_quotes_gpc() ) {
            $str = stripslashes($str);
                    }

        return $this->dbh->escape_string($str);
    }

    /**
     * Return mySQL specific system date syntax
     * Oracle: SYSDATE Mysql: NOW()
     * @return string
     */
    protected function sysdate()
    {
        return 'NOW()';
    }

    /**
     * Perform mySQL query and try to determine result value
     * @param string $query
     * @return int|object|array
     */
    protected function query(string $query = "")
    {
        // This keeps the connection alive for very long running scripts
        if ( $this->count(false) >= 500 ) {
            $this->disconnect();
            $this->quick_connect($this->dbuser,$this->dbpassword,$this->dbname,$this->dbhost,$this->dbport,$this->encoding);
        }

        // Initialise return
        $return_val = 0;

        // Flush cached values..
        $this->flush();

        // For reg expressions
        $query = trim($query);

        // Log how the function was called
        $this->func_call = "\$db->query(\"$query\")";

        // Keep track of the last query for debug..
        $this->last_query = $query;

        // Count how many queries there have been
        $this->count(true, true);

        // Start timer
        $this->timer_start($this->num_queries);

        // Use core file cache function
        if ( $cache = $this->get_cache($query) )
        {
            // Keep tack of how long all queries have taken
            $this->timer_update_global($this->num_queries);

            return $cache;
        }

        // If there is no existing database connection then try to connect
        if ( ! isset($this->dbh) || ! $this->dbh )
        {
            $this->connect($this->dbuser, $this->dbpassword, $this->dbhost, $this->dbport);
            $this->select($this->dbname,$this->encoding);
            // No existing connection at this point means the server is unreachable
            if ( ! isset($this->dbh) || ! $this->dbh || $this->dbh->connect_errno )
                return $return_val;
        }

        // Perform the query via std mysql_query function..
        $this->result = @$this->dbh->query($query);

        // If there is an error then take note of it..
        if ( $str = @$this->dbh->error )
        {
            $this->register_error($str);
            if ($this->show_errors == true){
                trigger_error($query);
            }
            static::logWrite($query,static::TypeLogWrite["ERR"]);
            return false;
        }

        // Query was an insert, delete, update, replace
        if ( preg_match("/^(insert|delete|update|start|replace|truncate|drop|create|alter|begin|commit|rollback|set|lock|unlock|call)/i",$query) )
        {
            $is_insert = true;
            $this->rows_affected = @$this->dbh->affected_rows;

            // Take note of the insert_id
            if ( preg_match("/^(insert|replace)\s+/i",$query) )
            {
                $this->insert_id = @$this->dbh->insert_id;
            }

            // Return number fo rows affected
            $return_val = $this->rows_affected;
        }
        // Query was a select
        else
        {
            $is_insert = false;

            // Take note of column info
            $i=0;
            while ($i < @$this->result->field_count)
            {
                $this->col_info[$i] = @$this->result->fetch_field();
                $i++;
            }

            // Store Query Results
            $num_rows=0;
            while ( $row = @$this->result->fetch_object() )
            {
                // Store relults as an objects within main array
                $this->last_result[$num_rows] = $row;
                $num_rows++;
            }

            @$this->result->free_result();

            // Log number of rows the query returned
            $this->num_rows = $num_rows;

            // Return number of rows selected
            $return_val = $this->num_rows;
        }


        // disk caching of queries
        $this->store_cache($query,$is_insert);


        // Keep tack of how long all queries have taken
        $this->timer_update_global($this->num_queries);

        return $return_val;

    }

    /**
     * Close the active mySQLi connection
     */
    protected function disconnect()
    {
        $this->conn_queries = 0;
        @$this->dbh->close();
    }

    public function getVersion()
    {
        // TODO: Implement getVersion() method.
        if ( ! isset($this->dbh) || ! $this->dbh ) {
            $this->connect($this->dbuser, $this->dbpassword, $this->dbhost, $this->dbport);
            if($this->select($this->dbname,$this->encoding) == false){
                throw new \Exception("dose connect mysql failed.");
            }
        }

        return $this->dbh->get_server_info();
    }
}
