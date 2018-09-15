<?php
namespace App\Form\FieldTypes;

class TextAreaType extends \App\Form\FieldTypes\BaseType
{

    public function __construct()
    {
        $this->setDefaults([
            "required" => false,
            "rule" => "text",
            "class" => "",
            "class_valid" => "",
            "class_error" => "",
            "placeholder" => "",
            "label" => ""
        ]);
        $this->type = "textarea";
    }

    public function generateView()
    {
        $this->input = "";
        if ($this->getOption("label")) {
            $this->input = "<label for=\"{$this->getName()}\" >" . $this->getOption("label") . "</label>\n";
        }
        $this->input .= "<textarea name=\"{$this->getName()}\"";
        ($this->getOption("id")) ? $this->input .=" id=\"{$this->getOption("id")}\"" : "";
        ($this->getOption("class")) ? $this->input .=" class=\"{$this->getOption("class")}\"" : "";
        ($this->getOption("rows")) ? $this->input .=" rows=\"{$this->getOption("rows")}\"" : "";
        ($this->getOption("cols")) ? $this->input .=" cols=\"{$this->getOption("cols")}\"" : "";
        ($this->getOption("disabled")) ? $this->input .=' disabled': '';
        ($this->getOption("readonly")) ? $this->input .=' readonly ' : '';
        /// Just HTML5
        ($this->getOption("form")) ? $this->input .=" form=\"{$this->getOption("form")}\"" : "";
        ($this->getOption("max-lenght")) ? $this->input .=" maxlenght=\"{$this->getOption("max-lenght")}\"" : "";
        ($this->getOption("placeholder")) ? $this->input .=" placeholder=\"{$this->getOption("placeholder")}\"" : "";
        ($this->getOption("autofocus")) ? $this->input .=' autofocus ' : '';
        ($this->getOption("required")) ? $this->input .=' required="required"': '';
        $this->input .= " >" . htmlspecialchars($this->getValue()) . "</textarea>\n";
        return $this->input;
    }

}