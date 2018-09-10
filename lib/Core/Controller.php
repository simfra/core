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

    public function render()
    {
        echo "asdasd";
    }
    
    
    public function getKernel()
    {
        return $this->kernel;
    }
    
    
    
    public function loadModel($name)
    {
        //echo "Name: $name " .  __NAMESPACE__ . "   ". (new \ReflectionClass(get_called_class()))->getShortName() ;
        $class = $this->getKernel()->getApplicationNamespace() . "Model\\" . (new \ReflectionClass(get_called_class()))->getShortName();
        if (method_exists(new $class($this->kernel), $name)) {
            return (new $class($this->kernel))->{$name}();
        } else {
            throw new FatalException("Controller", "Unable to load model ($name)");
        }
    }
    
    
    public function callControllerMethod($name)
    {
        if (method_exists($this, $name)) {
            return new Response($this->{$name}());
        } else {
            throw new FatalException("Controller", "No method exists ($name) in this class");
        }
    }
}
