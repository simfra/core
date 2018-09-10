<?php
namespace Core;

use Core\Exception\FatalException;

class Container
{
    private $bundles = [];
    private $services = [];

    public function __construct()
    {
        return $this;
    }

    public function getBundle($name)
    {
        if (array_key_exists($name, $this->bundles)) {
            return $this->bundles[$name];
        } else {
            throw new FatalException("Bundle", "No bundles with specified name ($name)");
        }
    }

    public function addBundle($bundle, $name = "")
    {
        $reflection = new \ReflectionClass($bundle);
        if ($name == "") { // default name will be class name
            $name = $reflection->getShortName();
        }
        if (!array_key_exists($name, $this->bundles)) {
            $this->bundles[$name] = $bundle;
        } else {
            throw new FatalException("Container", "Bundle ($bundle) is already registered with name: $name");
        }
        return $this->bundles[$name];
    }

    public function isBundle($bundle)
    {
        return isset($this->bundles[$bundle]) ? true : false;
    }

    public function delBundle($bundle)
    {
        if ($this->isBundle($bundle)) {
            unset($this->bundles[$bundle]);
        }
    }


    public function listBundles()
    {
        return $this->bundles;
    }


    public function getBundleConfig($name, $config)
    {
        if (!array_key_exists("bundles", $config)) {
            return [];
        }
        foreach ($config['bundles'] as $key => $value) {
            if (mb_strtolower($key) == mb_strtolower($name)) {
                return $config['bundles'][$key];
            }
        }
        return [];
    }


    /**
     * @param $service_name
     * @return mixed
     * @throws FatalException
     */
    public function getService($service_name)
    {
        return (array_key_exists($service_name, $this->services))
            ? $this->services[$service_name] : $this->instantiate($service_name);
    }

    /**
     * @param $class_name
     * @return mixed
     * @throws FatalException
     */
    private function instantiate($class_name)
    {
        try {
            $reflection = new \ReflectionClass($class_name);
        } catch (\ReflectionException $a) {
            throw new FatalException("Failed dependency load", "Unable to load dependency. "
                . $a->getMessage() . " for class: ". $class_name);
        }
        $constructor = $reflection->getConstructor();
        if (!$constructor) { // If there is no constructor - add simple object
            return $this->addService($reflection->newInstance(), $reflection->getShortName());
        }
        $dependencies = [];
        $params = $constructor->getParameters();
        foreach ($params as $param) {
            try {
                $class = $param->getClass();
            } catch (\ReflectionException $exception) {
                throw new FatalException("Failed dependency load", "Unable to load dependency. "
                    . $exception->getMessage() . " for class: " . $class_name);
            }
            if ($class) {
                if ($class->name == get_class($this)) { // if dependency is an instance of App\Bootstrap
                    $dependencies[] = $this;
                } elseif ($this->isBundle($class->getShortName())) { // dependency could be loaded before as a bundle
                    $dependencies[] = $this->getBundle($class->getShortName());
                } else {
                    $dependencies[] = $this->getService($class->name);
                }
            }
        }
        return $this->addService($reflection->newInstanceArgs($dependencies), $reflection->getShortName());
    }

    public function addService($object, $name = "")
    {
        if (array_key_exists($name, $this->services)) {
            return $this->services[$name];
        } else {
            return $this->services[$name] = $object;
        }
    }


}