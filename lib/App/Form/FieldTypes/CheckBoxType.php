<?php
namespace App\Form\FieldTypes;

class CheckBoxType extends \App\Form\FieldTypes\BaseType
{
    public $show_label = true;

    public function __construct()
    {
        $this->setDefaults([
            "required" => false,
            "rule" => "bool",
            "class" => "",
            "class_valid" => "",
            "class_error" => "",
            "placeholder" => "",
            "label" => ""
        ]);
        $this->type = "checkbox";
    }

}