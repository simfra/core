<?php
namespace Core\Exception;

class FatalException extends \Exception
{
    protected $Title;
    protected $Name;
    protected $Debug;
    protected $Class;
    protected $Function;
    public function __construct($Title, $Message, $Code = 0, $Previous = null)
    {
        $this->Title = $Title;
        $this->Name = __CLASS__;
        $this->Debug = debug_backtrace();
        parent::__construct($Message, $Code, $Previous);
    }
    
    public function getTitle()
    {
        return $this->Title;
    }
    
    public function getName()
    {
        return $this->Name;
    }
    
    public function getDebug()
    {
        return $this->Debug;
    }
    
    public function getClass()
    {
        return $this->Class;
    }
    
    public function getFunction()
    {
        return $this->Function;
    }


    public function getStatusCode()
    {
        return  500;
    }
}