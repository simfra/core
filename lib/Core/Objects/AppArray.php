<?php
namespace Core\Objects;

use Core\Interfaces\ArrayMethod;

class AppArray implements ArrayMethod
{
    private  $values = [];
    public function __call($name, $aa)
    {
        // return "assdas";
    }
    
    public function __invoke()
    {
        return $this;
    }

    public function __get($variable)
    {
        if (isset($this->values[$variable])) {
            return $this->values[$variable];
        } else {
            return null;
        }
    }

    public function __debugInfo()
    {
        return $this->values;
    }
    
    
    public function getAll()
    {
        return $this->values;
    }


    public function __toString()
    {
        return $this->values;//print_r($this->values, true);
    }
        
    
    public function __construct($initial_values = "")
    {
        if ($initial_values != "") {
            $this->values = $initial_values;
        }
    }
    
    public function get($variable = "")
    {
        if (trim($variable)!="" && isset($this->values[$variable])) {
            return $this->values[$variable];
        } else {
            $debug = debug_backtrace();
            trigger_error("Undefined object property ($variable) - Line: ". $debug[0]['line'] . " File: " .$debug[0]['file'] ." Function: ". $debug[0]['function'], E_USER_NOTICE);
            return null;
        }
    }
    
    public function set($variable, $value)
    {
        $this->values[$variable] = $value;
    }
    
    public function add($value)
    {
        $this->values[] = $value;
    }

    public function isset($variable)
    {
        if (isset($this->values[$variable])) {
            return true;
        } else {
            return false;
        }
    }
}
