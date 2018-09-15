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
    private $_kernel = null;
    private $_connection = null;
    private $host = "93.159.184.104";
    private $port = "5432";
    private $_dbname = "spedgo_dev";
    private $_user = "postgres";
    private $_password = "SamSam12345";
    private $_program_name = "";

    private function connect()
    {

        $this->_connection = pg_connect("host=$this->host port=$this->port dbname=$this->_dbname user=$this->_user password=$this->_password options='--application_name=$this->_program_name'");
        if ($this->_connection) {
            return;
        } else {
            throw new FatalException("Database","Unable to connect to database ");
        }
    }


    function __construct(\Core\Debug\Debug $kernel = null)
    {
        //echo "Database ";
        //die("Database " . print_r($kernel),true);
        //$this->_kernel = $kernel;
    }

    public function __toString()
    {
        return "DATABASE";
    }
    /**
     * base::query()
     * 
     * @param string $string - Query to execute
     * @return $result array - Return array if succeed or false if error 
     */
    public function query($string, $params = array())
    {
        if($this->_connection === null) {
            $this->connect();
        }        
        $database = $this->_connection;
        
        $result = false;
        $time_start = microtime(true);
        if (count($params)) {
            $result = pg_query_params($database, $string, $params);
        } else {
            $result = pg_query($database, $string);
        }
        // DO DEBUG
        if(2==1)
        {
        $temp['time'] = round(microtime(true)-$time_start,3);
        $temp['query'] = $string;
        $debug = debug_backtrace();
        if(count($debug)>0)
        {
        if($debug[0]['class']!="baza") { // ABY POMIJALO FUNKCJE WYWOLYWANE Z TEJ KLASY, GDY WYWOLANE ZOSTANIE np UPDATE .. to ta funkcja wywola funkcje query z tej klasy wiec błednie bedzie pokazywac poprzednia klase - nie ta z której faktycznie przyszlo zapytanie do bazy
            $nr = 0;
        }
        elseif($debug[1]['class']!="baza") {
            $nr=1;
        }
        else {
            $nr=2;
        }
        $temp['query_file'] = $debug[$nr]['class'];
        $temp['query_function'] = $debug[$nr]['function'];
        }
        if($result==false){
            $temp['result']=false;
        }
        else {
            $temp['result']=true;
        }
        if(isset($app->debug['database']['time']))
        {
            $app->debug['database']['time'] += $temp['time'];
        }
        else
        {
            $app->debug['database']['time']= $temp['time'];
        }
        $app->debug['database'][]= $temp;
        }
        
        $handle = fopen(PATH_LOG . "baza.log", "a+");
        fwrite($handle, "" . date("d-m-Y H:i:s") . " | " .  $temp['query_file'] . $temp['query_function'] .$string . "\n");
        fclose($handle);
        
        return $result;
    }


    /**
     * base::fetch_all_object()
     * 
     * @param array $fields - Array of fields to fetch
     * @return array $wynik - Array of fetched results as a array of objects 
     */
    public function fetch_all_object($fields)
    {
        if (count($fields) > 0) {
            $wynik = array();
            while ($row = pg_fetch_object($fields)) {
                $wynik[] = $row;
            }
            return $wynik;
        } else
            return false;

    }


    public function fetch_assoc($r)
    {
        $wynik = array();
        while ($row = pg_fetch_assoc($r)) {
            $wynik[] = $row;
            //print_r($row);
        }
        return $wynik;
    }


    /**
     * base::insert()
     * 
     * @param string $table - Nazwa tablicy do której maja być wstawiane wartości
     * @param array $fields - Array wartości ( $key => $value ) które mają być wstawiane
     * @param array $extra - Dodatkowa klauzula .. np. RETURNING id  
     * @return boolean - True jeżeli wykonano poprawnie INSERT lub False gdy wystąpił błąd
     */
    public function insert($table, $fields = array(), $extra = "")
    {
        if (count($fields)>0) {
            $keys = array_keys($fields);
            $values = '';
            $x = 1;
            foreach ($fields as $field) {
                $values .= '$' . $x . ' ';
                if ($x < count($fields)) {
                    $values .= ', ';
                }
                $x++;
            }
            $query = "INSERT INTO $table (" . implode(', ', $keys) . ") VALUES($values) $extra";
            return $this->query($query, $fields);
        }
        return false;
    }

    /**
     * base::update()
     * 
     * @param string $table - Nazwa tabeli do UPDATE
     * @param array $fields - Lista pól do uaktualnienia
     * @param string $where - Warunek WHERE 
     * @return boolean True jeżeli nastąpiło poprawne uaktualnienie lub False gdy wystąpił błąd
     */
    public function update($table, $fields = array(), $where = '')
    {
        $set = '';
        $x = 1;
        foreach ($fields as $name => $value) {
            $set .= "$name = '$value'";
            if ($x < count($fields)) {
                $set .= ', ';
            }
            $x++;
        }


        $sql = "UPDATE $table SET $set WHERE $where";
        return $this->query($sql);
        //        echo "UPDATE $table SET $set WHERE $condition";
        return false;
    }

    /**
     * base::insert_get_query()
     * 
     * Funkcja zwraca treść zapytania SQL. Potrzebowałem do logowania błędu ;)
     */
    public function insert_get_query($table, $fields = array(), $extra = "")
    {
        $keys = array_keys($fields);
        
        $x = 1;
        $values = '';
        foreach ($fields as $field => $value) {
            $values .= "'". $value."'";
            if ($x < count($fields)) {
                $values .= ', ';
                
            }
            $x++;

        }
        $query = "INSERT INTO $table (" . implode(', ', $keys) . ") VALUES($values) $extra";
        return $query;
    }

    /**
     * base::update_get_query()
     * 
     * Funkcja zwraca treść zapytania SQL. Potrzebowałem do logowania błędu ;)
     */
    public function update_get_query($table, $fields = array(), $where = '')
    {
        $set = '';
        $x = 1;
        foreach ($fields as $name => $value) {
            $set .= "$name = '$value'";
            if ($x < count($fields)) {
                $set .= ', ';
            }
            $x++;
        }
        $sql = "UPDATE $table SET $set WHERE $where";
        return $sql;
    }
    
    
    /**
     * base::rowCount()
     * 
     * @param mixed $set
     * @return
     */
    public function rowCount($set)
    {
        return pg_num_rows($set);
    }
    
    

}
