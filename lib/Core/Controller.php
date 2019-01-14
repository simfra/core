<?php
namespace Core;

use Core\Exception\FatalException;
use Core\Http\Response\Response;

class Controller
{
    private $kernel = null;
    protected $tpl = null;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function render($template)
    {
        //echo "asdasd";
        return $this->getTemplate()->fetch($template);
    }
    
    
    public function getKernel()
    {
        return $this->kernel;
    }

    public function getPage()
    {
        return $this->getKernel()->page;
    }

    public function getBundle($bundle)
    {
        return $this->getContainer()->getBundle($bundle);
    }

    public function getContainer()
    {
        return $this->getKernel()->getContainer();
    }

    public function getTemplate()
    {
        return $this->getBundle("View");
    }


    public function loadModel($name, $params = "")
    {
        //echo "Name: $name " .  __NAMESPACE__ . "   ". (new \ReflectionClass(get_called_class()))->getShortName() ;
        $class = $this->getKernel()->getApplicationNamespace() . "Model\\" . (new \ReflectionClass(get_called_class()))->getShortName();
        //echo $class;
        if (method_exists(new $class($this->kernel), $name)) {
            return (new $class($this->kernel))->{$name}($params);
        } else {
            throw new FatalException("Controller", "Unable to load model ($name). Class: $class");
        }
    }
    
    
    public function callControllerMethod($name, $params = "")
    {
        if (method_exists($this, $name)) {
            return new Response($this->{$name}($params));

        } else {
            throw new FatalException("Controller", "No method exists ($name) in this class");
        }
    }


    public function getParams()
    {
        return $this->getKernel()->page->params;
    }


    public function loadController($name, $method, $params = "")
    {
        $controller_name = $this->getKernel()->getApplicationNamespace() . "Controller\\$name";
        $controller = new $controller_name($this->getKernel());
        return $controller->callControllerMethod($method, $params);
    }
}
