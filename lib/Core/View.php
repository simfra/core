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
    private $plugins = [];
    public $default_assigned = false;


    private function getTemplate()
    {
        $this->force_compile = true;
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
            //$this->template->
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
        if (!file_exists($dir . $template)) {
            throw new TemplateException("View", "Template file: " . $dir . $template . " has not been found!");
            //return "Template file: " . $dir . $template . " has not been found!";
        }
        try {
            $page = $this->getKernel()->page;
            // Assigning page variables to template
            $lang = $page->lang;
            if (!$this->default_assigned) {
                $this->getTemplate()->assign("title", (isset($page->struct->title[$lang])) ? $page->struct->title[$lang] : "");
                $this->getTemplate()->assign("meta_title", (isset($page->struct->meta_title[$lang])) ? $page->struct->meta_title[$lang] : "");
                $this->getTemplate()->assign("meta_description", (isset($page->struct->meta_description[$lang])) ? $page->struct->meta_description[$lang] : "");
                $this->default_assigned = true;
                if (!isset($this->getTemplate()->tpl_vars["content"])) {
                    $this->getTemplate()->assign("content", (isset($page->struct->content[$lang])) ? $this->getTemplate()->fetch("string:" . $page->struct->content[$lang]) : "");
                }
                $this->getTemplate()->assign("PUBLIC_DIR", $_SERVER['DOCUMENT_ROOT']);//$this->getKernel()->getPublicPath());
                $this->getTemplate()->assign("APP_DIR", APP_DIR);
                $this->getTemplate()->assign("GET", $_GET);
            }
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


    public function registerPlugin($type, $name, $callback)
    {
        //$this->getTemplate()->regis
        //echo "rejestracja plugina <pre>". var_dump($this->get_registered_plugins()) ."</pre>";
            return $this->getTemplate()->registerPlugin($type, $name, $callback);
    }
}