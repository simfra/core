<?php
namespace my_app\Controller;

class front extends \Core\Controller
{
    
    public function index()
    {
        // call only proper method in model and return response to main controller
        return $this->loadModel("index");
    }

}