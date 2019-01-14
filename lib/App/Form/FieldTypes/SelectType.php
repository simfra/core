<?php
namespace App\Form\FieldTypes;

class SelectType extends \App\Form\FieldTypes\BaseType
{
    public $choices = [];
    public function __construct()
    {
        $this->setDefaults([
            "required" => false,
            "rule" => "alphanum",
            "class" => "",
            "class_valid" => "",
            "class_error" => "",
            "placeholder" => "",
            "label" => "",
            "option_label" => "Choose an option",
            "show_label" => true
        ]);
        $this->type = "select";
    }

    public function addChoice($value, $label)
    {
        $this->choices[$value] = $label;
        return $this;
    }

    public function addChoices($array)
    {
        if(is_array($array) && count($array) >0 ) {
            foreach ($array as $value => $label) {
                $this->choices[$value] = $label;
            }
        }
        return $this;
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

    public function fromXml($xml)
    {
    }

    public function toXml()
    {
        $temp[] = "<field>";
        $temp[] = "<name>{$this->getName()}</name>";
        $temp[] = "<type>{$this->getClass()}</type>";
        $temp[] = "<options>";
        foreach ($this->getOptions() as $key => $option) {
            $temp[] = "<$key>$option</$key>";
        }
        $temp[] = "</options>";
        $temp[] = "<choices>";
        foreach ($this->getChoices() as $key => $choice) {
            $temp[] = "<$choice>$key</$choice>";
        }
        $temp[] = "</choices>";
        $temp[] = "</field>";
        return implode("\n", $temp);
    }

    public function getName()
    {
       return $this->name;
    }

    public function generateView()
    {
        if ($this->hasErrors()) {
            $this->setOption("class", $this->getOption("class"). ($this->getOption("class_error") != "") ? $this->getOption("class_error"): " error");
        }
        if ($this->isValid) {
            $this->setOption("class", $this->getOption("class"). (($this->getOption("class_valid") != "") ? $this->getOption("class_valid"): ""));
        }
        $name = ($this->getOption("multiple")) ? $this->getName() . "[]" : $this->getName();
        if ($this->getOption("wrapped")) {
            $this->input = "<div class=\"".$this->getOption("wrapped_class")."\">";
        } else {
            $this->input = "";
        }
        if ($this->getOption("label")) {
            $this->input .= "<label for=\"$name\" >" . $this->getOption("label") . "</label>";
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
        if ($this->getOption("show_label") === true) {
            $this->input .= "<option " . ((empty($this->getValue())) ? "selected='selected'" : '') . " value=''>{$this->getOption("option_label")}</option>";
        }
        foreach ($this->choices as $value => $label) {
            if ((is_array($this->getValue()) && in_array($value, $this->getValue())) || (!is_array($this->getValue()) && $value == $this->getValue()) ) {
                $selected = " selected='selected' ";
            } else {
                $selected = "";
            }
            $this->input .= "<option data-value=\"{$this->getValue()}\" data-value=\"{$value}\" value=\"{$value}\" $selected>{$label}</option>";
        }
        $this->input .="</select>";
        ($this->hasErrors()) ? $this->input .= "<span>". implode(" ", $this->getErrors()) ."</span>" : "";
        ($this->getOption("wrapped")) ? $this->input .="</div>" : "";
        return $this->input;
    }

    public function getChoices()
    {
        return $this->choices;
    }

}