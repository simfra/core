<?php
namespace App\Form\FieldTypes;

class SelectType extends \App\Form\FieldTypes\BaseType
{
    public $choices = [];
    public $option_label = "Choose an option";
    public $show_label = true;
    public function __construct()
    {
        $this->setDefaults([
            "required" => false,
            "rule" => "alphanum",
            "class" => "",
            "class_valid" => "",
            "class_error" => "",
            "placeholder" => "",
            "label" => ""
        ]);
        $this->type = "select";
    }

    public function addChoice($value, $label)
    {
        $this->choices[$value] = $label;
    }

    public function addChoices($array)
    {
        if(is_array($array) && count($array) >0 ) {
            foreach ($array as $value => $label) {
                $this->choices[$value] = $label;
            }
        }
    }


    public function setValue($value)
    {
        if ($this->getOptions("multiple") === true) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $this->value[] = $val;
                }
            } else {
                $this->value[] = $value;
            }
        } else {
            $this->value = $value;
        }
    }

    public function getName()
    {
       return $this->name;
    }

    public function generateView()
    {
        $name = ($this->getOption("multiple")) ? $this->getName() . "[]" : $this->getName();
        $this->input = "";
        if ($this->getOption("label")) {
            $this->input = "<label for=\"$name\" >" . $this->getOption("label") . "</label>\n";
        }
        $this->input .= "<select name=\"$name\"";
        ($this->getOption("id")) ? $this->input .=" id=\"{$this->getOption("id")}\"" : "";
        ($this->getOption("class")) ? $this->input .=" class=\"{$this->getOption("class")}\"" : "";
        ($this->getOption("disabled")) ? $this->input .= " disabled='disabled' " : "";
        ($this->getOption("size")) ? $this->input .=" size=\"{$this->getOption("size")}\"" : "";
        ($this->getOption("multiple")) ? $this->input .=' multiple': '';
        /// Just HTML5
        ($this->getOption("form")) ? $this->input .=" form=\"{$this->getOption("form")}\"" : "";
        ($this->getOption("autofocus")) ? $this->input .=' autofocus="autofocus" ' : '';
        ($this->getOption("required")) ? $this->input .=' required="required"': '';
        $this->input .= " >";
        if ($this->show_label) {
            $this->input .= "<option " . ((empty($this->getValue())) ? "selected='selected'" : '') . " value=''>{$this->option_label}</option>";
        }
        foreach ($this->choices as $value => $label) {
            $this->input .= "<option  value=\"{$value}\"" . (((is_array($this->getValue()) && in_array($value, $this->getValue())) or (!is_array($this->getValue()) && $value == $this->getValue()))  ? " selected='selected' " : "") . ">{$label}</option>";
        }
        $this->input .="</select>\n";
        return $this->input;
    }

}