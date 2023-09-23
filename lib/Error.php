<?php
namespace /;
/**
 * Error
 * 
 * @package 
 * @author Pawel Synarecki
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class Error
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
        $this->Class = $this->Debug[1]['class'];
        $this->Function = $this->Debug[1]['function'];
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
    
} 