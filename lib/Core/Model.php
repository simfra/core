<?php
namespace Core;

class Model
{
    
    private $kernel = null;
    
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }
    
    
    public function getKernel()
    {
        return $this->kernel;
    }

    public function getBundle($bundle)
    {
        return $this->getContainer()->getBundle($bundle);
    }

    public function getContainer()
    {
        return $this->getKernel()->getContainer();
    }

    public function render(string $template)
    {
        //$this->kernel->getBundle("Config");
        //$this->getBundle("View")->assign("title1", "Pawel");



        return $this->getBundle("View")->fetch($template);
    }

    public function register_plugin($type, $name, $callback)
    {
        //return $this->getBundle("View")->register_function($type, $name, $callback);
    }

    public function getRequest()
    {
        return $this->getKernel()->page->request->query->args->GET;
    }

    public function getService($service_name, $singleton = false)
    {
        //die($service_name);
        return $this->getContainer()->getService($service_name, $singleton);
    }

    public function getTemplate()
    {
        return $this->getBundle("View");
    }
    public function registerPlugin($type, $name, $callback)
    {
        //echo  "rejestracja plugina";
        //return $this->getBundle("View")->registerPlugin($type, $name, $callback);
        return $this->getBundle("View")->registerPlugin($type, $name, $callback);
    }

    public function getParams()
    {
        return $this->getKernel()->page->params;
    }


    public function getConfig($variable = "")
    {
        $config = $this->getKernel()->config;
        if ($variable == "") {
            return $config;
        } else {
            if (array_key_exists($variable, $config)) {
                return $config[$variable];
            } else {
                trigger_error("Variable $variable doesn't exists in config", E_USER_NOTICE);
                return "";
            }
        }
    }

}