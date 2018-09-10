<?php
namespace Core;

use Core\Exception\FatalException;
use Core\Exception\TemplateException;
use Smarty;

class View extends Bundle
{
    private $template = null;
    public $templateDir = APP_DIR . "templates/";//__DIR__ . "/../../templates";
    public $compileDir =  APP_DIR . "cache/templates";

    public function __constr1uct()
    {
        //$this->getKernel()->getApplicationPath();
        //echo "<pre>";
        //print_r($this);//debug_backtrace());
        //echo "</pre>";
        //$this->template = new Smarty;
        //$this->template->setTemplateDir($this->templateDir)->setCompileDir($this->compileDir);
        //Smarty::muteExpectedErrors();
    }

    private function getTemplate()
    {
        if ($this->template === null) {
            $this->template = new Smarty;
            if (trim($this->compileDir) != "" && !file_exists($this->compileDir)) {
                if (!mkdir($this->compileDir, 0777, true)) {
                    die('Failed to create folder: ' . $this->compileDir . debug_print_backtrace());
                }
            } elseif (trim($this->compileDir) == "") {
                throw new TemplateException("View", "No compile folder set '" . $this->compileDir . "'");
            }
            $this->template->setTemplateDir($this->templateDir)->setCompileDir($this->compileDir);
            Smarty::muteExpectedErrors();
        }
        return $this->template;
    }
    
    public function assign($key, $value)
    {
        $this->getTemplate()->assign($key, $value);
    }
    
    
    public function fetch($template, $dir = APP_DIR . "templates/", $params = "")
    {
        try {
            return $this->getTemplate()->fetch($dir . $template, $params);
        } catch (\Exception $e) {
            throw new TemplateException("View", $e->getMessage());
        }
    }

    public function assignByRef($key, $value)
    {
        $this->getTemplate()->assignByRef($key, $value);
    }
    
    public function get_template_vars()
    {
        return $this->getTemplate()->getTemplateVars();
    }
    
    public function register_object()
    {
        //return $this->_template->assign_by_ref();
    }
}