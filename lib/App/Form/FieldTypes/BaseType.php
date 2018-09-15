<?php
namespace App\Form\FieldTypes;

abstract class BaseType
{
    protected $name = "";
    protected $options = [];
    protected $type = "";
    protected $value = "";
    protected $input = "";

    public function __construct()
    {
        $this->setDefaults([
            "required" => false,
            "rule" => "alpha",
            "class" => "",
            "class_valid" => "",
            "class_error" => "",
            "placeholder" => "",
            "label" => ""
        ]);
        $this->type = "text";
    }
    
    public function setDefaults($defaults)
    {
        foreach ($defaults as $key => $value) {
            $this->options[$key] = $value;
        }
        $this->generateView();
        return $this;
    }

    public function unsetOption($key)
    {
        if (array_key_exists($key, $this->options)) {
            unset($this->options[$key]);
        }
        return $this;
    }
    
    public function getOption($key)
    {

        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }
    }

    
    public function generateView()
    {
        //echo var_dump($this->getOption("autocomplete"));

        $this->input = "";//print_r($this->options, true) ."";
        if ($this->getOption("label")) {
            $this->input = "<label for=\"$this->name\" >" . $this->getOption("label") . "</label>\n";
        }
        $this->input .= "<input name=\"{$this->name}\" value=\"{$this->value}\"";
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
            $this->input .= " >\n";
        return $this->input;
    }


    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setOption($key, $value)
    {
        if (trim($value) !== "") {
            $this->options[$key] = $value;
        }
        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    public function getValue()
    {
        return $this->value;
    }


    public function getType()
    {
        return $this->type;
    }

}