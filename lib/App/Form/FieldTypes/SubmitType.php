<?php
namespace App\Form\FieldTypes;

class SubmitType extends \App\Form\FieldTypes\BaseType
{
    public function __construct()
    {
        $this->setDefaults([
            "required" => true,
            "rule" => "alphanum",
            "class" => "",
            "class_valid" => "",
            "class_error" => "",
        ]);
        $this->type = "submit";
        $this->submit = "submit";
        //$this->value = $this->name;
    }

    public function generateView()
    {
        if ($this->getOption("wrapped")) {
            $this->input = "<div class=\"".$this->getOption("wrapped_class")."\">";
        } else {
            $this->input = "";
        }
        $this->input .= "<input value=\"{$this->value}\" name=\"{$this->name}\"";
        (!empty($this->type)) ? $this->input .= " type=\"{$this->type}\"" : "";
        ($this->getOption("class")) ? $this->input .=" class=\"{$this->getOption("class")}\"" : "";
        $this->input .= " >";
        return $this->input;
    }


}