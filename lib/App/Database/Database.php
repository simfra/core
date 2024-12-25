<?php
namespace App\Database;

//use Core\Objects\App_Array;
//use Core\Objects\App_Object;
//use \Core\Interfaces\test_interface;
//use App\Bundle;
use Core\Exception\FatalException;
use Core\Bundle;

class Database extends Bundle
{
    private $connection = null;
    private $host = "localhost";
    private $port = "3306";
    private $dbname = "";
    private $username = "";
    private $password = "";
    private $program_name = "";
    private $type = "Mysql";
    private $db = null;


    public function __construct($type = "")
    {
        if ($type != "") {
            $this->type = $type;
        }
    }

    /**
     * @throws FatalException
     */
    private function connect()
    {

        $this->connection = $this->db;//->connect();
        if ($this->connection) {
            return;
        } else {
            throw new FatalException("Database","Unable to connect to database ");
        }
    }


    /**
     * @throws FatalException
     */
    public function getDb()
    {
        if ($this->db === null) {
            $class = "\App\Database\Types\\" . $this->type;
            if (class_exists($class)) {
                $this->db = new $class($this->host, $this->username, $this->password, $this->dbname, $this->port);
                //print_r($this->db);
            } else {
                throw new FatalException("Form", "Unknown database type:  $this->type");
            }
        }
        return $this->db;
    }

    /**
     * base::query()
     *
     * @param string $string - Query to execute
     * @return false|mixed $result array - Return array if succeed or false if error
     * @throws FatalException
     */
    public function query(string $string)
    {
        $time_start = microtime(true);
        $result = $this->getDb()->query($string);
        if ($this->isBundle("Debug") && !$this->getKernel()->isProd) {
            $app = $this->getBundle("Debug");
            $temp['time'] = round(microtime(true) - $time_start, 3);
            $app->database['time'] += $temp['time'];
            $temp['query'] = $string;
            $debug = debug_backtrace();
            if (count($debug) > 0) {
                if ($debug[0]['class'] != get_class($this)) { // ABY POMIJALO FUNKCJE WYWOLYWANE Z TEJ KLASY, GDY WYWOLANE ZOSTANIE np UPDATE .. to ta funkcja wywola funkcje query z tej klasy wiec błednie bedzie pokazywac poprzednia klase - nie ta z której faktycznie przyszlo zapytanie do bazy
                    $nr = 0;
                } elseif ($debug[1]['class'] != get_class($this)) {
                    $nr = 1;
                } else {
                    $nr = 2;
                }
                $temp['query_file'] = $debug[$nr]['class'];
                $temp['query_function'] = $debug[$nr]['function'];
            }
            ($result === false) ? $temp['result'] = false : $temp['result'] = true;
            $temp['error_message'] = $this->getLastError();
            $temp['rows_count'] = $this->getDb()->get_affected_rows();
            $app->addDatabaseLog($temp);
        }
        return $result;
/*
        $handle = fopen(PATH_LOG . "baza.log", "a+");
        fwrite($handle, "" . date("d-m-Y H:i:s") . " | " . $temp['query_file'] . $temp['query_function'] . $string . "\n");
        fclose($handle);
        return $result; */
    }


    /**
     * @throws FatalException
     */
    public function getLastError()
    {
        return $this->getDb()->getLastError();
    }

    public function select($table, $columns = "", $where = "", $return = "", $extra = '')
    {
        $query = $this->getDb()->select($table, $columns, $where, $extra);
        if($return != "") {
            //return $this->query($query);

            return $this->fetchAll($this->query($query), $return);
        } else {
            return $this->query($query);
        }
    }


    public function insert($table, $insert_values)
    {
        $query = $this->getDb()->insert($table, $insert_values);
        echo $query;
        if ($this->query($query) === true) {
            return mysqli_insert_id($this->getDb()->getConnection());
        } else {
            return false;
        }
    }

    /**
     * @param $table - name of table to update
     * @param $fields - array of values to update - [key => value] where key is a column name
     * @param string $where - where condition
     * @return -1 when update error, or int of affected rows
     */
    public function update($table, $fields, $where='')
    {
        $query = $this->getDb()->update($table, $fields, $where);
        if ($this->query($query) == true ) {
            return $this->getDb()->get_affected_rows();
        } else {
            return -1;
        }
    }

    public function delete($table, $where='')
    {
        $query = $this->getDb()->delete($table, $where);
        if ($this->query($query) == true ) {
            return $this->getDb()->get_affected_rows();
        } else {
            return -1;
        }
    }

    public function fetchAll($result, $method="object")
    {
        if (!$result) {
            return false;
        }
        switch($method) {
            default:
                return $result->fetch_all(MYSQLI_BOTH);
            case "assoc":
                return $result->fetch_all(MYSQLI_ASSOC);
            case "num":
                return $result->fetch_all(MYSQLI_NUM);
            case "object":
                $rows = [];
                while($row = $result->fetch_object()) {
                    $rows[] = $row;
                }
                return $rows;
        }
    }



}