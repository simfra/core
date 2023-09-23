<?php
namespace App\Form\FieldTypes;

class PasswordType extends \App\Form\FieldTypes\BaseType
{
    public $show_label = true;

    public function __construct()
    {
        $this->setDefaults([
            "required" => true,
            "rule" => "alphanum",
            "class" => "",
            "class_valid" => "",
            "class_error" => "",
            "placeholder" => "",
            "label" => ""
        ]);
        $this->type = "password";
    }

}