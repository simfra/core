<?php
namespace App\Form\FieldTypes;

abstract class BaseType
{
    protected $name = "";
    protected $options = [];
    protected $type = "";
    protected $value = "";
    protected $input = "";
    protected $errors = [];
    protected $isValid = false;
    protected $wrapped = false;
    protected $wrapped_class= "";
    protected $data = [];
    const TYPE = __CLASS__;

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

    public function reset()
    {
        $this->errors = [];
        $this->isValid = false;
        $this->value = "";
    }

    public function setGroup($name)
    {
        $this->options['group'] = $name;
        return $this;
    }

    public function getGroup()
    {
        return $this->getOption("group");
    }

    public function addError($error)
    {
        echo "Dodano blad: $error";
        $this->errors[] = $error;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
       return (count($this->errors) == 0) ?  false :  true;
    }

    public function countErrors()
    {
        return count($this->errors);
    }
    
    public function setDefaults($defaults)
    {
        foreach ($defaults as $key => $value) {
            if ($key == "data") {
                $this->data = $value;
            } else {
                $this->options[$key] = $value;
            }
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

    public function setValid($value)
    {
        $this->isValid = $value;
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
            ($this->getOption("class_label")) ? $class_label =" class=\"{$this->getOption("class_label")}\"" : $class_label = "";
            $this->input .= "<label for=\"$this->name\" $class_label>" . $this->getOption("label") . "</label>";
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
            ($this->getOption("readonly")) ? $this->input .=' readonly': '';
            if (count($this->data)>0) {
                foreach($this->data as $key => $value) {
                    $this->input .= " data-$key=$value ";
                }
            }
            $this->input .= " >";
        ($this->getOption("error_class")) ? $temp_class = " class='".$this->getOption("error_class") . "'" : $temp_class = "";
        ($this->hasErrors()) ? $this->input .= "<span$temp_class>". implode(" ", $this->getErrors()) ."</span>" : "";
        ($this->getOption("wrapped")) ? $this->input .="</div>" : "";
        return $this->input;
    }

    public function wrapInDiv($value, $class = "")
    {
        $this->setOption("wrapped",$value);
        $this->setOption("wrapped_class", $class);
        return $this;
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

    public function getLabel()
    {
        return $this->getOption("label");
    }

    public function getClass()
    {
        return (new \ReflectionClass($this))->getShortName();
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
        if (isset($this->choices)) {
            $temp[] = "<choices>";
            foreach ($this->getChoices() as $key => $choice) {
                $temp[] = "<$choice>$key</$choice>";
            }
            $temp[] = "</choices>";
        }
        $temp[] = "</field>";
        return implode("\n", $temp);
    }


}