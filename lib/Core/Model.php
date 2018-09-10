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
        return $this->getBundle("View")->fetch($template);
    }

    public function getService($service_name)
    {
        return $this->getContainer()->getService($service_name);
    }

    public function getTemplete()
    {
        return $this->getBundle("View");
    }



}