<?php
namespace my_app\Model;

class front extends \Core\Model
{

    public function index()
    {
        $smarty = $this->getTemplate();
        return $smarty->fetch("front/front.tpl");
    }

}