<?php

namespace Core;

abstract class Bundle
{
    private $container = null;
    private $kernel = null;
    private $booted = false;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function getKernel()
    {
        return $this->kernel;
    }

    public function bootUp(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->booted = true;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getBundle($bundle)
    {
        return $this->container->getBundle($bundle);
    }

    public function isBundle($bundle)
    {
        if ($this->booted) {
            return $this->container->isBundle($bundle);
        } else {
            return false;
        }
    }

    public function defaultConfig($config)
    {
        //$reflection = new \ReflectionClass($this);
        foreach ($config as $key => $value) {
            try {
                $prop = new \ReflectionProperty(get_class($this), $key);
                $prop->setAccessible(true);
                $prop->setValue($this, $value);
            } catch (\Exception $e) {
                $e = null;
            }
//            if (isset($this->$key)) {
//                $property = $reflection->getProperty($key);
//                if ($property instanceof ReflectionProperty) {
//                    $property->setValue($this, $value);
//                }
//            } else {
//                $this->$key = $value;
//            }
        }
    }

//    public function getRootDir()
//    {
//        return $this->getKernel()->getApplicationPath();
//    }
}