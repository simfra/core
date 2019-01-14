<?php
namespace App\Form;

//use App\Form\FieldTypes\BaseType;
use Core\Exception\FatalException;
use DateTime;

class Form
{
    private $name;
    private $id;
    private $class;
    private $method;
    private $action;
    private $submit;
    public  $errors = [];
    public $fields = array();
    public $form = "";
    
    /**
     * Form::__construct()
     * Domyslne wartosci dla formularza: name = 'submit', method = 'get', submit = 'submit', id i klasa dla formularz nie wymagana
     * @return
     */
    public function __construct($form = [])
    {
        (isset($form['name']) ? $this->name = $form['name'] : $this->name = "submit");
        (isset($form['id']) ? $this->id = $form['id'] : "");
        (isset($form['class']) ? $this->class = $form['class'] : "");
        (isset($form['method']) ? $this->method = $form['method'] : $this->method = "get");
        (isset($form['action']) ? $this->action = $form['action'] : $this->action = "");
        (isset($form['submit']) ? $this->submit = $form['submit'] : $this->submit = "submit");
        $this->addField($this->name, "SubmitType",["name" => $this->name])->setValue($this->submit);
        return $this;
    }


    public function getSubmit()
    {
        return $this->submit;
    }
    
    public function getAction()
    {
        return $this->action;
    }
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function getId()
    {
        return $this->id;
    }
    public function getClass()
    {
        return $this->class;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function addField($name, $type, $attr = [])
    {
        return $this->fields[] = $this->createField($name, $type, $attr);
    }


    public function loadXml($file)
    {
        $file = __DIR__ . "/Schema/$file";
        //echo $file;
        if (file_exists($file)) {
            $xml = @simplexml_load_file($file);
            if ($xml === false) {
                foreach (libxml_get_errors() as $error) {
                //    echo "<br>", $error->message;
                }
            } else {
                ($xml->name) ? $this->name = $xml->name : "";
                ($xml->id != "") ? $this->id = $xml->id : "";
                ($xml->class) ? $this->class = $xml->class : "";
                ($xml->method) ? $this->method = $xml->method : "";
                ($xml->action) ? $this->action = $xml->action : "";
                ($xml->submit) ? $this->submit = $xml->submit : "";
                foreach($xml->fields->field as $field)
                {
                    $temp = $this->addField($field->name, $field->type, (array)$field->options);
                        if ($field->choices) {
                            foreach ((array)$field->choices as $label => $value) {
                                $temp->addChoice($value, $label);
                            }
                        }
                }
            }
        } else {
            trigger_error("File: $file doesn't exists or you don't have permissions to open this file");
        }
    }

    public function reset()
    {
        foreach($this->fields as $field) {
            if($field->getType()!= "submit") {
                $field->reset();
            }
            unset($_POST);
            //$_POST = [];
            unset($this->errors);

        }
    }

    public function saveFormToXml($filename = "", $dir = __DIR__ . "/Schema/")
    {
        $templates = __DIR__ . "/Templates/";
        $smarty = new \Smarty();
        $smarty->setCompileDir(APP_DIR . "/cache");
        $smarty->assign("form", $this);
        $xml = new \DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $xml->loadXML($smarty->fetch($templates . "FormXml.tpl"));
        file_put_contents($dir . "$filename", $xml->saveXML());
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        $this->generateView();
        return $this->form;
    }

    public function generateView()
    {
        //ob_start(); Turn on on production

        if (isset($_POST)) {
            $this->assignValues($_POST);
            //print_r($_POST);
        }
        $this->form = "";
        $this->form .= "<form name='$this->name' method='$this->method' action='$this->action'";
            (!empty($this->class)) ? $this->form .= " class='$this->class'" : "";
            (!empty($this->id)) ? $this->form .= " id='$this->id'" : "";
            $this->form .= " >\n";
            $submit_input = "";
        foreach ($this->fields as $field) {
            if ($field->getType() != "submit") {
                $this->form .= $field->generateView();
            } else {
                $submit_input .= $field->generateView();
            }
        }
        $this->form .= $submit_input;
        // submit button
        //if ()
        //$this->form .= "<input type='submit' name='" . $this->name . "'";
        //(isset($this->submit)) ? $this->form .= " value='" . $this->submit ."' " :  "";
        //$this->form .= "/>\n";
        $this->form .= "</form>\n";
        //ob_end_clean();
        return $this->form;
    }

    private function hasSubmitField()
    {
        foreach ($this->fields as $key => $field) {
            if ($field['type'] == "submit") {
                return true;
            }
        }
        return false;
    }

    public function assignValues($table)
    {
        //print_r($table);
        foreach ($this->fields as $key => $field) {
            $name = trim($field->getName(),"[]");
            //echo "Name $name ";
            if (isset($table[$name])) {
                //echo "przekazano do ustawiania wartosci: ". print_r($table[$name]) ."\n";
                $field->setValue($table[$name]);
            }
        }
    }



    private function createField($name, $type, $defaults = [])
    {
        if (mb_strpos($type,"\\") === false) {
            $class = "\App\Form\FieldTypes\\" . $type;
        } else {
            $class = $type;
        }
//echo "<br/>".$class ."<br/>";
        if (class_exists($class)) {
            //echo "Adadsasda $class<br/>";
            return
                (new $class)
                ->setName($name)
                ->setDefaults($defaults);
        } else {
            throw new FatalException("Form", "Unknown field type  \"$type\"");
        }
    }

    public function getValues()
    {
        $result = [];
        foreach ($this->fields as $key => $field) {
            if ($field->getType() != "submit") {
                $result[$field->getName()] = $field->getValue();
            }
        }
        return $result;
    }


    public function validateField($field)
    {
        if (!$field instanceof \App\Form\FieldTypes\BaseType) {
            trigger_error($this->errors["form"][] = "Field: " . get_class($field) . " is not correct form field. Must be instance of 
                \App\Form\FieldTypes\BaseType", E_USER_WARNING);
            return;
        }

        if ($field->getType() == "submit") {
            return;
        }

        $value = (!is_array($field->getValue()) ? trim($field->getValue()) : array_map("trim", $field->getValue()));
        $required = $field->getOption("required");
        if ($value == "" && $required == true) {
            $field->addError(($field->getOption("error_require") != null ) ? $field->getOption("error_require") :"Field required");
            return ;
        } elseif ($value != "") {
            if ( $field->getOption("min-length") != null && (mb_strlen($value) < $field->getOption("min-length")) ) {
                $field->addError(($field->getOption("error_min_length")) ? $field->getOption("error_min_length") : "Value length to short (Min-length: " . $field->getOption("min-length") . ")");
            }
            if ( $field->getOption("max-length") != null && (mb_strlen($value) > $field->getOption("max-length")) ) {
                $field->addError(($field->getOption("error_max_length")) ? $field->getOption("error_max_length") : "Value length to long (Max-length: " . $field->getOption("max-length") . ")");
            }
            if ($field->getOption("rule") === null ) {
                $field->addError("No validation rule set for this field");
                return ;
            } else {
                $this->checkRule($field);
            }
        }

    }

    public function addError($error)
    {
        $this->errors['form'][] = $error;
    }

    public function checkRegex($pattern, $values)
    {
        $match_count = 0;
        if(is_array($values)) {
            foreach ($values as $value) {
                if (!preg_match($pattern, $value)) {
                    $match_count++;
                }
            }
        } else {
            if (!preg_match($pattern, $values)) {
                $match_count++;
            }
        }
        return ($match_count == 0) ? false : true;
    }

    public function checkRule($field)
    {
        //echo "Check rule:::: rule: " . $field->getOption("rule")." <br/>";
        switch ($field->getOption("rule")) {
            default:
                $field->addError("No validation rule set for this field");
                return false;
                break;
            case "alpha":
                //if ($this->checkRegex('/^[a-zA-z\s\.]{1,}$/', $field->getValue())) {
                if ($this->checkRegex('/^[-\' \p{L}]+$/u', $field->getValue())) {
                    $field->addError(($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value");
                    return false;
                }
                break;
            case "alphanum":
                if ($this->checkRegex('/^[a-zA-z\s\.0-9]{1,}$/', $field->getValue())) {
                    $field->addError(($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value");
                    return false;
                }
                break;
            case "numeric":
                if (!is_numeric($field->getValue())) {
                    $field->addError(($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value");
                    return false;
                }
                break;
            case "regex":
                if($field->getOption("rule_param") == null) {
                    $field->addError("You must specify regex param (as 'rule_param' option)");
                    return false;
                }
                if (preg_match($field->getOption("rule_param"), $field->getValue())) {
                    $field->addError(($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value");
                    return false;
                }
                break;
            case "mail":
            case "email":
                if ($this->checkRegex('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', $field->getValue())) {
                    $field->addError(($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value");
                    return false;
                }
                break;
            case "phone":
                //if (!$this->checkRegex('#^[a-zA-Z0-9]+[_a-zA-Z0-9-]*(\.[_a-z0-9-]+)*@[a-z0-9]+(-[a-z0-9]+)*(\.[a-z0-9-]+)*(\.[a-z]{2,6})$#i', $field->getValue())) {
                if ($this->checkRegex('/^(((\+44\s?\d{4}|\(?0\d{4}\)?)\s?\d{3}\s?\d{3})|((\+44\s?\d{3}|\(?0\d{3}\)?)\s?\d{3}\s?\d{4})|((\+44\s?\d{2}|\(?0\d{2}\)?)\s?\d{4}\s?\d{4}))(\s?\#(\d{4}|\d{3}))?$/', $field->getValue())) {
                    //echo "bład";
                    $field->addError(($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value");
                    return false;
                }
                break;
            case "float":
                if ($this->checkRegex('/^[0-9]+(.[0-9]+)?$/', $field->getValue())) {
                    $field->addError(($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value");
                    return false;
                }
                break;
            case "url":
                // https://gist.github.com/dperini/729294
                if ($this->checkRegex('%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu', $field->getValue())) {
                    if ($this->checkRegex('/^[\/]?[a-zA-Z_\-0-9]*[\/]?$/i', $field->getValue())) {
                        $field->addError(($field->getOption("error_rule") != null) ? $field->getOption("error_rule") : "Wrong value");
                        return false;
                    }
                }
                break;
            case "text":
                if (!$this->checkRegex('/^[A-Za-z0-9_~\-!@#\$%\^&\*\(\)]+$\/\/^[A-Za-z0-9_~\-!@#\$%\^&\*\(\)]+$/', $field->getValue())) { // '/^[-a-zA-Z0-9\s.:,]*$/'
                    $field->addError(($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value");
                    return false;
                }
                break;
            case "bool":
                if ( !$field->getValue() == "off" &&  !$field->getValue() == false && !$field->getValue() === true && !$field->getValue() === 0) { // '/^[-a-zA-Z0-9\s.:,]*$/'
                    $field->addError(($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value");
                    return false;
                }
                break;
            case "any":
                return true;
                break;
        }
        $field->setValid(true);
        return true;
    }
    
    public function getFields($group_name = "")
    {
        // moving submit field to end of fields array
        foreach ($this->fields as $key => $field) {
            if($field->getType()== "submit") {
                $temp = $field;
                unset($this->fields[$key]);
                $this->fields[] = $temp;
            }
        }
        if ($group_name != "") {
            $temp = [];
            foreach($this->fields as $key => $field) {
                if ($field->getGroup() == $group_name) {
                    $temp[] = $field;
                }
            }
            return $temp;
        }
        return $this->fields;
    }

    public function getHeader()
    {
        ($this->getId() != "") ? $id="id='{$this->getId()}'" : $id = "";
        return "<form class='{$this->getClass()}' method='{$this->getMethod()}' action='{$this->getAction()}' name='{$this->getName()}' $id >";
    }

    public function getFooter()
    {
        return "</form>";
    }

    public function isSend()
    {
//        print_r($this->name);
        switch(strtolower($this->method))
        {
            case "post":
                if (isset($_POST[$this->name])) {
                    return true;
                } else {
                    return false;
                }
                break;
            case "get":
                if (isset($_GET[$this->name])) {
                    return true;
                } else {
                    return false;
                }
                break;
        }
        if (isset($_POST[$this->name])) {
            return true;
        }
        return false;
    }

    public function isCorrect()
    {
        if ($this->isSend() && !$this->hasErrors()) {
            return true;
        } else {
            return false;
        }
    }

    public function process(callable $call = null)
    {
        if ($this->isSend()) {
            //echo "formularz wyslany";
            $this->assignValues($_POST);
            foreach ($this->fields as $field) {
                $this->validateField($field);
            }

            if (!$this->hasErrors()) {
                //echo "Formularz bez błedow";
                if ($call != null) {
                    if (is_callable($call)) {
                        return call_user_func($call, $this);
                    } else {
                        throw new FatalException("Form", "Incorrect callable function name passed");
                    }
                }
            } else {
                //echo "formularz zawiera błedy";
            }

        }

    }

    /**
     * Form::sendOK()
     * Funkcja sprawdza czy formularz został wyslany i zawiera bledy walidacji
     * @return true jezeli wyslany i nie zawiera bledow, false gdy nie wyslany lub zawiera bledy
     */
    public function sendOK()
    {
        if($this->hasErrors()==0 && $this->isSend() == true)
        {
            return true;
        }
        return false;
    }


    public function getFieldByName($name)
    {
        foreach ($this->fields as $field) {
            if (strtolower($field->getName()) == strtolower($name)) {
                return $field;
            }
        }
        return false;
    }


    /**
     * Form::clearVars()
     * Oczyszczanie zmiennych z tablicy
     * @param mixed $table
     * @return
     */
    public function clearVars($table)
    {
        $clear = array();
        foreach ($table as $key => $wartosc)
        {
            if(is_array($wartosc))
            {
                $tmp = array();
                foreach($wartosc as $w)
                {
                    $tmp[] = trim($w);
                }
                $clear[$key] = $tmp;
            }else{
                $clear[$key] = trim($wartosc);
            }            
        }
        return $clear;
    }


    /*******************************************************************************************************/

    /**
     * Form::addDefaults()
     * @param mixed $defaults
     * @return void
     */
    public function addDefaults($defaults)
    {
        foreach($defaults as $key => $value) {
            if ($this->getFieldByName($key) != false) {
                $this->getFieldByName($key)->setValue($value);
            }
        }
    }

    /**
     * Form::hasErrors()
     * Return true when form has an errors
     * @return
     */
    public function hasErrors($key = "")
    {
        if($key == "form" && isset($this->errors['form'])) {
            return true;
        }
        if (count($this->errors) > 0 ) {
            return true;
        }
        foreach ($this->fields as $field) {
            if ($field->hasErrors() && $key == "" && $key != "form") {
                return true;
            } elseif ($field->getName() == $key) {
                return true;
            }
        }
        return false;
    }
    

    public function getErrors($key = "")
    {
        $temp = $this->errors;
        foreach ($this->fields as $field) {
            if ($field->hasErrors()) {
                $temp[$field->getName()] = $field->getErrors();
            }
        }
        if ($key != "" && isset($temp[strtolower($key)])) {
            return $temp[strtolower($key)];
        } else {
            return $temp;
        }
    }

    public function setValues($values)
    {
        foreach($values as $key => $value)
        {
            if ($this->getFieldByName($key)) {
                $this->getFieldByName($key)->setValue($value);
            }
        }
    }

}

