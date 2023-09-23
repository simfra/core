<?php
namespace App\Form\FieldTypes;

class HiddenType extends \App\Form\FieldTypes\BaseType
{
    public $show_label = false;

    public function __construct()
    {
        $this->setDefaults([
            "required" => true,
            "rule" => "any",
            "class" => "",
            "class_valid" => "",
            "class_error" => "",
            "placeholder" => "",
            "label" => ""
        ]);
        $this->type = "hidden";
    }

}