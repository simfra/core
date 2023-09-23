<?php
namespace App\Database\Types;

use Core\Exception\FatalException;

class Mysql
{
    private $connection = null;
    private $host = "localhost";
    private $port = "3306";
    private $dbname = "";
    private $username = "";
    private $password = "";

    public function __construct($host, $username, $password, $dbname, $port)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->port = $port;
    }

    public function getConnection()
    {
        if(!$this->connection) {
            $this->connect();
        }
        return $this->connection;
    }

    public function connect()
    {
        if ($this->dbname == "") {
            throw new FatalException("Database","You must specify Database name");
        }
        $this->connection = mysqli_connect($this->host, $this->username, $this->password, $this->dbname, $this->port);
        if ($this->connection) {
            return $this->connection;
        } else {
            throw new FatalException("Database","Unable to connect to database ($this->dbname)");
        }
    }

    public function query($string)
    {
        if(!$this->connection) {
            $this->connect();
        }
        $database = $this->connection;
        $result = $database->query($string);
        //die(var_dump($result));
        return $result;
    }

    public function select($table, $columns, $where, $extra = "")
    {
        if ($columns == "") {
            $query = "SELECT * FROM $table ";
        } else if (is_array($columns)) {
            $query = "SELECT " . implode(",", $columns) . " FROM $table ";
        } else {
            $query = "SELECT $columns FROM $table ";
        }
        ($where != "") ? $query .= " WHERE $where" : "";
        ($extra != "") ? $query .= " $extra" : "";
        //die($query);
        return $query;
    }

    public function insert($table, $insert_values)
    {
        $query = "INSERT INTO $table ";
        $keys = [];
        $values = [];
        foreach ($insert_values as $key => $value) {
            $keys[] = "`".$key."`";
            if (is_string($value)) {
                $values[] = "'" . mysqli_real_escape_string($this->getConnection(), $value) . "'";
            } else {
                $values[] = "" . mysqli_real_escape_string($this->getConnection(), $value) . "";
            }
        }
        $query .= "(" . implode("," , $keys) .")";
        $query .= " VALUES (" . implode(",", $values) . ")";
        return $query;
    }

    /**
     * @param $table - name of table to update
     * @param $fields - array of values to update - [key => value] where key is a column name
     * @param string $where - where condition
     * @return -1 when update error, or int of affected rows
     */
    public function update($table, $fields, $where='')
    {
        $query = "UPDATE $table SET ";
        $sets = [];
        foreach($fields as $key => $value) {
            $sets[] = " `$key`='" . mysqli_real_escape_string($this->getConnection(),$value). "'";
        }
        $query .= implode(",", $sets);
        ($where != "") ? $query .= " WHERE $where" : "";
        return $query;
    }

    public function delete($table, $where): string
    {
        $query = "DELETE FROM $table ";
        ($where != '') ? $query .= " where $where" : "";
        return $query;
    }

    public function get_affected_rows()
    {
        return mysqli_affected_rows($this->connection);
    }


    public function getLastError()
    {
        return mysqli_error($this->connection);
    }


    public function fetch_all($result, $method)
    {
        switch($method) {
            default:
                return $result->fetch_all(MYSQLI_BOTH);
                break;
            case "assoc":
                return $result->fetch_all(MYSQLI_ASSOC);
                break;
            case "num":
                return $result->fetch_all(MYSQLI_NUM);
                break;
            case "object":
                $rows = [];
                while($row = $result->fetch_object()) {
                    $rows[] = $row;
                }
                return $rows;
                break;
        }
    }

}