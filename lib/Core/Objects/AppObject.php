<?php
namespace Core\Objects;

use Core\Interfaces\ObjectMethod;

class AppObject implements ObjectMethod
{
    private $values = [];
    
    public function __construct($initial_values = "")
    {
        if ($initial_values!="") {
            foreach ($initial_values as $key => $value) {
                if (is_array($value)) {
                    $this->add($key, new AppArray($value));
                } else {
                    $this->$key = $value;
                }
            }
        }
    }
    
    
    public function __get($variable)
    {
        if (isset($this->values[$variable])) {
            return $this->values[$variable];
        } else {
            return null;
        }
    }
    
    public function __toString()
    {
        return print_r($this, true);
    }


    public function get($variable = "")
    {
        if (isset($this->values[$variable])) {
            return $this->values[$variable];
        } else {
            $debug = debug_backtrace();
            trigger_error("Undefined object property ($variable) - Line: ". $debug[0]['line'] . " File: " .$debug[0]['file'] ." Function: ". $debug[0]['function'], E_USER_NOTICE);
            return null;
        }
    }
    
    
    public function add($key, $val)
    {
        $this->values[$key] = $val;
    }
    
    public function getAll()
    {
        $ret_array = array();
        foreach ($this as $key => $value) {
            if($value instanceof AppObject  || $value instanceof AppArray) {
            $ret_array[$key] = $value->getAll();
            } else {
                $ret_array[$key] = $value;
            }
        }
        return $ret_array;
    }
    
    public function set($variable, $value)
    {
        $this->values[$variable] = $value;
    }
}