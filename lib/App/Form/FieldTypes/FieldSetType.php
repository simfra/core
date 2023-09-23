<?php
namespace App\Form\FieldTypes;

class FieldSetType extends \App\Form\FieldTypes\BaseType
{
    public $start;
    public function __construct()
    {
        $this->setDefaults([
            $start = true
        ]);
        $this->type = "break";
        $this->value = 1;
    }

    public function generateView()
    {
        if ($this->getOption("start") == true) {
            $this->input = "<fieldset>";
        } else {
            $this->input = "</fieldset>";
        }
        return $this->input;
    }


}