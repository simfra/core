<?php
namespace App\Form\FieldTypes;

class CheckBoxType extends \App\Form\FieldTypes\BaseType
{
    public $show_label = true;
    public $checked = false;
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

    public function generateView()
    {
        if ($this->hasErrors()) {
            $this->setOption("class", $this->getOption("class"). ($this->getOption("class_error") != "") ? $this->getOption("class_error"): " error");
        }
        if ($this->isValid) {
            $this->setOption("class", $this->getOption("class"). (($this->getOption("class_valid") != "") ? $this->getOption("class_valid"): ""));
        }
        if ($this->getOption("wrapped")) {
            $this->input = "<div class=\"".$this->getOption("wrapped_class")."\">";
        } else {
            $this->input = "";
        }
        if ($this->getOption("label")) {
            $this->input .= "<label for=\"$this->name\" >" . $this->getOption("label") . "</label>";
        }
        $this->input .= "<input name=\"{$this->name}\" " ;//
        ($this->checked) ?  $this->input .= "checked=\"checked\"" : "";
        (!empty($this->type)) ? $this->input .= " type=\"{$this->type}\"" : "";
        ($this->getOption("id")) ? $this->input .=" id=\"{$this->getOption("id")}\"" : "";
        ($this->getOption("class")) ? $this->input .=" class=\"{$this->getOption("class")}\"" : "";
        ($this->getOption("max-lenght")) ? $this->input .=" maxlenght=\"{$this->getOption("max-lenght")}\"" : "";
        ($this->getOption("disabled")) ? $this->input .=' disabled': '';
        /// Just HTML5
        ($this->getOption("placeholder")) ? $this->input .=" placeholder=\"{$this->getOption("placeholder")}\"" : "";
        ($this->getOption("autocomplete")) ? $this->input .=' autocomplete="on"': '';
        ($this->getOption("required")) ? $this->input .=' required="required"': '';
        ($this->getOption("autofocus")) ? $this->input .=' autofocus': '';
        $this->input .= " >";
        ($this->hasErrors()) ? $this->input .= "<span>". implode(" ", $this->getErrors()) ."</span>" : "";
        ($this->getOption("wrapped")) ? $this->input .="</div>" : "";
        return $this->input;
    }

    public function setValue($value)
    {
        $this->checked = $value;
        $this->value = $value;
        return $this;
    }
}