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
    private $isSend = false;
    private $_postData = array();
    public  $errors = [];
    private $dane = array();
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

    public function getFieldByName($name)
    {
        foreach ($this->fields as $field) {
            if ($field->getName() == $name) {
                return $field;
            }
        }
        return false;
    }

    public function loadXml($file)
    {
        $file = __DIR__ . "$file";
        if (file_exists($file)) {
            //$content = file($file);
            $xml = simplexml_load_file($file);
            if ($xml === false) {
                foreach (libxml_get_errors() as $error) {
                    echo "<br>", $error->message;
                }
            } else {
                print_r($xml);
            }
        } else {
            echo "Plik nie istnieje";
        }
    }

    public function saveFormToXml($filename = "", $dir = __DIR__ . "/Schema/")
    {
        $templates = __DIR__ . "/Templates/";
        $smarty = new \Smarty();
        $smarty->assign("form", $this);
        file_put_contents($dir . "$filename", $smarty->fetch($templates . "FormXml.tpl"));
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
        foreach ($this->fields as $field) {
            $this->form .= $field->generateView();
        }
        // submit button
        $this->form .= "<input type='submit' name='" . $this->name . "'";
        (isset($this->submit)) ? $this->form .= " value='" . $this->submit ."' " :  "";
        $this->form .= "/>\n";
        $this->form .= "</form>\n";
        //ob_end_clean();
        return $this->form;
    }

    public function assignValues($table)
    {
        foreach ($this->fields as $key => $field) {
            $name = trim($field->getName(),"[]");
            if (isset($table[$name])) {
                //echo "przekazano do ustawiania wartosci: ". print_r($table[$name]) ."\n";
                $field->setValue($table[$name]);
            }
        }
    }

    private function createField($name, $type, $defaults = [])
    {
        $class = "\App\Form\FieldTypes\\" . $type;
        if (class_exists($class)) {
            echo "Adadsasda $class<br/>";
            return
                (new $class)
                ->setName($name)
                ->setDefaults($defaults);
        } else {
            throw new FatalException("Form", "Unknown field type  \"$type\"");
        }
        //return null;
    }


    public function validateField($field)
    {
        if (!$field instanceof \App\Form\FieldTypes\BaseType) {
            trigger_error($this->errors["form"][] = "Field: " . get_class($field) . " is not correct form field. Must be instance of 
                \App\Form\FieldTypes\BaseType", E_USER_WARNING);
            return;
        }
        $name = $field->getName();
        $value = (!is_array($field->getValue()) ? trim($field->getValue()) : array_map("trim", $field->getValue()));
        $required = $field->getOption("required");
        $rule = $field->getOption("rule");
        if ($value == "" && $required == true) {
            $this->errors[$name][] = ($field->getOption("error_require") != null ) ? $field->getOption("error_require") :"Field required";
            return ;
        } elseif ($value != "") {
            if ( $field->getOption("min-length") != null && (mb_strlen($value) < $field->getOption("min-length")) ) {
                $this->errors[$name][] = ($field->getOption("error_min_length")) ? $field->getOption("error_min_length") : "Value length to short (Min-length: " . $field->getOption("min-length") . ")";
            }
            if ( $field->getOption("max-length") != null && (mb_strlen($value) > $field->getOption("max-length")) ) {
                $this->errors[$name][] = ($field->getOption("error_max_length")) ? $field->getOption("error_max_length") : "Value length to long (Max-length: " . $field->getOption("max-length") . ")";
            }
            //echo "**";
            //var_dump($field->getOption("rule"));
            //echo "**";
            if ($field->getOption("rule") === null ) {
                $this->errors[$field->getName()][] = "No validation rule set for this field";
                return ;
            } else {
                $this->checkRule($field);
            }
        }

    }

    public function checkRegex($pattern, $values)
    {
        $match_count = 0;
        if(is_array($values)) {
            foreach ($values as $value) {
                if (preg_match($pattern, $value)) {
                    $match_count++;
                }
            }
        } else {
            if (preg_match($pattern, $values)) {
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
                $this->errors[$field->getName()][] = "No validation rule set for this field";
                return false;
                break;
            case "alpha":
                if ($this->checkRegex('/[\'\"]/', $field->getValue())) {
                    $this->errors[$field->getName()][] = ($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value";
                    return false;
                }
                break;
            case "alphanum":
                if ($this->checkRegex('/[\'\"<>,.!?\r\t=+~`!@#$%^&;]/', $field->getValue())) {
                    $this->errors[$field->getName()][] = ($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value";
                    return false;
                }
                break;
            case "numeric":
                if (!is_numeric($field->getValue())) {
                    $this->errors[$field->getName()][] = ($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value";
                    return false;
                }
                break;
            case "regex":
                if($field->getOption("rule_param") == null) {
                    $this->errors[$field->getName()][] = "You must specify regex param (as 'rule_param' option)";
                    return false;
                }
                if ($this->checkRegex($field->getOption("rule_param"), $field->getValue())) {
                    $this->errors[$field->getName()][] = ($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value";
                    return false;
                }
                break;
            case "mail":
                if ($this->checkRegex('[^0-9A-Za-z]', $field->getValue())) {
                    $this->errors[$field->getName()][] = ($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value";
                    return false;
                }
                break;
            case "phone":
                if ($this->checkRegex('#^[a-zA-Z0-9]+[_a-zA-Z0-9-]*(\.[_a-z0-9-]+)*@[a-z0-9]+(-[a-z0-9]+)*(\.[a-z0-9-]+)*(\.[a-z]{2,6})$#i', $field->getValue())) {
                    $this->errors[$field->getName()][] = ($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value";
                    return false;
                }
                break;
            case "float":
                if ($this->checkRegex('/^[0-9]+(.[0-9]+)?$/', $field->getValue())) {
                    $this->errors[$field->getName()][] = ($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value";
                    return false;
                }
                break;
            case "url":
                // https://gist.github.com/dperini/729294
                if ($this->checkRegex('%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu', $field->getValue())) {
                    $this->errors[$field->getName()][] = ($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value";
                    return false;
                }
                break;
            case "text":
                if ($this->checkRegex('/^[A-Za-z0-9_~\-!@#\$%\^&\*\(\)]+$\/\/^[A-Za-z0-9_~\-!@#\$%\^&\*\(\)]+$/', $field->getValue())) { // '/^[-a-zA-Z0-9\s.:,]*$/'
                    $this->errors[$field->getName()][] = ($field->getOption("error_rule") != null ) ? $field->getOption("error_rule") :"Wrong value";
                    return false;
                }
                break;
        }
        return true;
    }
    
    public function getFields()
    {
        //$this->prepareFields();
        return $this->fields;
    }

    public function showForm($show = true)
    {
        $ret = [];
        $this->prepareFields();
        $ret['form'] = $this->fields;
        if (count($this->_errors) > 0) {
            $ret['errors'] = $this->_errors;
        }
        return $ret;
    }

    public function process()
    {
        if (isset($_POST[$this->name])) {
            $this->assignValues($_POST);
            foreach ($this->fields as $field) {
                $this->validateField($field);
            }
        }
//        echo "<pre>";
  //      print_r($this->errors);

    //    echo "</pre>";
        //if
    }

    private function makeInput($tablica)
    {
        $name = $tablica['name'];
        $errors = $this->_errors;
        if (isset($this->_postData[$name]))
        {
            $value = $this->_postData[$name];
                      
        } elseif (isset($tablica['default']))
        {
            $value = $tablica['default'];
        } else
        {
            $value = "";
        }

        $pole = "";
        
        if (isset($tablica['label']))
        {
            $pole .= "<label for=\"" . $tablica['name'] . "\" >" . $tablica['label'] . "</label>";
        }        
        if ($tablica['typ'] == "textarea")
        {
            $pole .= "<textarea name=\"" . $tablica['name'] . "\" ";
            if (isset($tablica['autocomplete']))
            {
                $pole .= "autocomplete=\"";
                if ($tablica['autocomplete'] == "on" || $tablica['autocomplete'] == 1)
                {
                    $pole .= "on\" ";
                }
                if ($tablica['autocomplete'] == "off" || $tablica['autocomplete'] == 0)
                {
                    $pole .= "off\" ";
                }
            }
            if (isset($tablica['id']))
            {
                $pole .= "id=\"" . $tablica['id'] . "\" ";
            }
            if(isset($errors[$name]) && isset($tablica['class_error']))
            {
                $pole .= "class=\"" . $tablica['class_error'] . "\" ";
            }else{
                if (isset($tablica['class']))
                {
                    $pole .= "class=\"" . $tablica['class'] . "\" ";
                }
            }
            if (isset($tablica['rows']))
            {
                $pole .= "rows=\"" . $tablica['rows'] . "\" ";
            }
            if (isset($tablica['cols']))
            {
                $pole .= "cols=\"" . $tablica['cols'] . "\" ";
            }
            if (isset($tablica['max-lenght']))
            {
                $pole .= "maxlength=\"" . $tablica['max-lenght'] . "\" ";
            }
            if (isset($tablica['placeholder']))
            {
                $pole .= "placeholder=\"" . $tablica['placeholder'] . "\" ";
            }
            $pole .= ">" . $value;
            $pole .= "</textarea>";
        } elseif ($tablica['typ'] == "button")
        {
            $pole .= "<button typ=\"" . $tablica['typ'] . "\" >$value";
            $pole .= "</button>";
        } elseif ($tablica['typ'] == "select")
        {
            $pole .= "<select ";
            if (isset($tablica['name']))
            {
                $pole .= "name=\"" . $tablica['name'];
                if (isset($tablica['multiple']) && $tablica['multiple'] == true)
                {
                    $pole .= "[]";
                }
                $pole .= "\" ";
            }
            if (isset($tablica['size']))
            {
                $pole .= "size=\"" . $tablica['size'] . "\" ";
            }
            if (isset($tablica['multiple']) && $tablica['multiple'] == true)
            {
                $pole .= " multiple ";
            }
            if (isset($tablica['disabled']) && $tablica['disabled'] == true)
            {
                $pole .= " disabled ";
            }
            if (isset($tablica['id']))
            {
                $pole .= "id=\"" . $tablica['id'] . "\" ";
            }
            if(isset($errors[$name]) && isset($tablica['class_error']))
            {
                $pole .= "class=\"" . $tablica['class_error'] . "\" ";
            }else if($this->isSend() == true && isset($tablica['class_ok']))
            {
                $pole .= "class=\"" . $tablica['class_ok'] . "\" ";
            }else{
                if (isset($tablica['class']))
                {
                    $pole .= "class=\"" . $tablica['class'] . "\" ";
                }
            }
            $pole .= ">";
            if (isset($tablica['options']))
            {

                foreach ($tablica['options'] as $key => $wartosc)
                {
                    $pole .= "<option ";
                    if (isset($wartosc['value']))
                    {
                        $pole .= "value=\"" . $wartosc['value'] . "\" ";
                    }                    
                    if (isset($this->_postData[$name]))
                    {
                        if(isset($tablica['multiple']) && $tablica['multiple'] == true)
                        {
                            if(in_array($wartosc['value'], $this->_postData[$name]))
                            {
                                $pole .= "selected ";
                            }
                        }else{
                            if($this->_postData[$name] == $wartosc['value'])
                            {
                                $pole .= "selected ";
                            }
                        }                        
                    } elseif (isset($tablica['default']) && $this->isSend($this->submit['name']) == false)
                    {
                        if(is_array($tablica['default']))
                        {
                            if(in_array($wartosc['value'], $tablica['default']))
                            {
                                $pole .= "selected ";
                            }
                        }else{
                            if($tablica['default'] == $wartosc['value'])
                            {
                                $pole .= "selected ";
                            }
                        }
                    }
                    if (isset($wartosc['disabled']))
                    {
                        $pole .= "disabled ";
                    }
                    $pole .= ">";
                    if (isset($wartosc['label']))
                    {
                        $pole .= $wartosc['label'];
                    }
                    $pole .= "</option>";
                }
            }
            $pole .= "</select>";
        } elseif ($tablica['typ'] == "multiselect")
        {          
            $ile_wybranych = 0;
            $checkboxy = $tablica['options'];
            if($this->isSend($this->submit['name']))
            {
                $wartosc = $value;
                $tmp = explode('&', $wartosc);
                $tmp2 = array();
                foreach($tablica['options'] as $key => $t)
                {
                    if(in_array($t['value'], $tmp))
                    {
                        $tmp2[] = $t['label'];
                        $checkboxy[$key]['checked'] = true;
                    }else{
                        $checkboxy[$key]['checked'] = false;
                    }
                }
                $ile_wybranych = count($tmp2);
            }else{
                $tmp = array();
                $tmp2 = array();
                foreach($tablica['options'] as $key => $t)
                {                    
                    if(isset($value[$t['value']]) && $value[$t['value']] == "t")
                    {
                        $tmp[] = $t['value'];
                        $tmp2[] = $t['label'];
                        $checkboxy[$key]['checked'] = true;
                    }else{
                        $checkboxy[$key]['checked'] = false;
                    }
                }
                $wartosc = implode('&', $tmp);
                $ile_wybranych = count($tmp2);
            }
            
            if($ile_wybranych > 0)
            {
                $label = implode(', ', $tmp2);
            }else{
                $label = $tablica['label_no_items'];
            }
            
            $pole .= "<span class=\"body_multiselect\"><input type=\"hidden\" name=\"" . $tablica['name'] . "\" value=\"$wartosc\" id=\"" . $tablica['id'] . "\"/>";
            
            $pole .= "<input type=\"button\" class=\"input_multiselect";
            if(isset($errors[$name]) && isset($tablica['class_error']))
            {
                $pole .= " " . $tablica['class_error'];
            }else if(isset($tablica['class']))
            {
                $pole .= " " . $tablica['class'];
            }
            $pole .= "\" id=\"multiselect_button\" data-input=\"" . $tablica['id'] . "\" value=\"" . $label . "\" data-no_items=\"" . $tablica['label_no_items'] . "\" />";
            
            $pole .= "<span class=\"span_multiselect\" id=\"multiselect_span_" . $tablica['id'] . "\">";
            foreach($checkboxy as $c)
            {
                $pole .= "<span class=\"multiselect_row\"><input class=\"checkbox_multiselect\" type=\"checkbox\" id=\"multiselect_checkbox\" data-multiselect=\"" . $tablica['id'] . "\" data-value=\"" . $c['value'] . "\"  data-text=\"" . $c['label'] . "\"";
                if($c['checked'] == true)
                {
                    $pole .= " checked";
                }                
                $pole .= "><label id=\"multiselect_label\" data-multiselect=\"" . $tablica['id'] . "\" data-value=\"" . $c['value'] . "\"  data-text=\"" . $c['label'] . "\">" . $c['label'] . "</label></span>";
            }
            
            $pole .= "</span></span>";
            
        } else
        { // DLA POZOSTALYCH POL
            $pole .= "<input name=\"" . $tablica['name'] . "\" ";
            if (isset($tablica['autocomplete']))
            {
                $pole .= "autocomplete=\"";
                if ($tablica['autocomplete'] == "on" || $tablica['autocomplete'] == 1)
                {
                    $pole .= "on\" ";
                }
                if ($tablica['autocomplete'] == "off" || $tablica['autocomplete'] == 0)
                {
                    $pole .= "off\" ";
                }
            }
            if (isset($tablica['typ']))
            {
                $pole .= "type=\"" . $tablica['typ'] . "\" ";
            }
            if (isset($tablica['id']))
            {
                $pole .= "id=\"" . $tablica['id'] . "\" ";
            }
            if(isset($errors[$name]) && isset($tablica['class_error']))
            {
                $pole .= "class=\"" . $tablica['class_error'] . "\" ";
            }else if($this->isSend() == true && isset($tablica['class_ok']))
            {
                $pole .= "class=\"" . $tablica['class_ok'] . "\" ";
            }else{
                if (isset($tablica['class']))
                {
                    $pole .= "class=\"" . $tablica['class'] . "\" ";
                }
            }
            if (isset($tablica['style']))
            {
                $pole .= "style=\"" . $tablica['style'] . "\" ";
            } 
            
            if (isset($tablica['max-lenght']))
            {
                $pole .= "maxlength=\"" . $tablica['max-lenght'] . "\" ";
            } 
            
            if (isset($tablica['placeholder']))
            {
                $pole .= "placeholder=\"" . $tablica['placeholder'] . "\" ";
            }        
            
            if (isset($tablica['disabled']) && $tablica['disabled'] == true)
            {
                $pole .= "disabled ";
            }   
            
            if($tablica['typ'] == "checkbox" || $tablica['typ'] == "radio")
            {
                if($value == 1 || $value == "on")
                {
                    $pole .= "checked value=\"$value\" />";
                }else{
                    $pole .= "value=\"1\" />";
                }  
            }
            else
            {
            $pole .= " value=\"$value\" />";
            }
        }

        return $pole;
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




    private function prepareFields()
    {
        // poczatek formularza
        $this->fields['start_input'] = sprintf('<form name="%s" action="%s" method="%s" ', $this->name, $this->action, $this->method);
        return ;
        $temp = array();
        $temp['start']['name'] = $this->name;
        $temp['start']['action'] = $this->action;
        $temp['start']['method'] = $this->method;
        $temp['start']['id'] = $this->id;
        $temp['start']['class'] = $this->class;
        $temp['start_input'] = "<form name=\"$this->name\" action=\"$this->action\" method=\"$this->method\" ";
        if(isset($this->id)){
            $temp['start_input'] .= "id=\"$this->id\" ";
        } 
        if(isset($this->class)){
            $temp['start_input'] .= "class=\"$this->class\" ";
        }         
        $temp['start_input'] .=" >";
        $temp['finish_input'] = "</form>";
        foreach ($this->fields as $key => $value)
        {
            $temp['fields'][$key] = $value;
            $temp['fields'][$key]['name'] = $value['name'];
            //$value['name'] = $value['name'];
            if (isset($this->_postData[$key]))
            {
                $temp['fields'][$key]['value'] = $this->_postData[$key];
            } elseif (isset($value['default']))
            {
                $temp['fields'][$key]['value'] = $value['default'];
            } else
            {
                $temp['fields'][$key]['value'] = "";
            }
            $temp['fields'][$key]['input'] = $this->makeInput($value);
        }
        $temp['fields']['finish']['input'] = "<input ".(isset($this->submit['class'])? 'class="'.$this->submit['class'].'"' : "class=\"btn btn-primary\"")." type=\"submit\" name=\"" . $this->submit['name'] . "\" value=\"" . $this->submit['name'] . "\" />"; //array("name" => $this->submit, "id" => "style", 'typ' => 'submit');
        $this->fields = $temp;
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


    /**
     * Form::sprawdz()
     * Funkcja do sprawdzania poprawnosci pol formularza. Nie wyswietla formularza
     * @return true jezeli poprawny, false jezeli zawiera bledy
     */
    public function sprawdz()
    {
        if ($this->isSend($this->submit['name']) == true)
        {
            $this->validate($_REQUEST, $this->fields);
            if ($this->hasErrors() == false) // GDY NIE MA BLEDOW
            {
                return true;
            }
            if ($this->hasErrors() == true) // GDY SA BLEDY
            {
                return false;
            }
        }
        return false;
    }

    public function reset()
    {
        foreach ($this->fields as $key => $value)
        {
            $this->_postData[$key] = "";
        }        
    }

    public function formExec()
    {
        $this->sprawdz();
        if($this->isSend($this->submit['name']))
        {
            if($this->hasErrors()==true )
            {
                return $this->showForm();
            }
            elseif($this->hasErrors()==false)
            {
                return true;
            }
        }
        return $this->showForm();
    }


    /*******************************************************************************************************/

    /**
     * Form::addDefaults()
     * @deprecated 
     * @param mixed $temp
     * @return void
     */
    public function addDefaults($temp)
    {
        foreach($temp as $key => $value)
        {
            $this->_postData[$key] = $value;
        }
    }


    /**
     * Form::getData()
     * @deprecated 
     * @return
     */
    public function getData()
    {
        $temp = $this->_postData;
        if (isset($_POST))
        {
            foreach ($this->fields as $key => $value)
            {
                if(isset($_POST[$key]))
                {
                    $temp[$key] = $_POST[$key];
                }
            }
        }
        return $temp;
    }

    /**
     * Form::hasErrors()
     * Zwraca liczbe bledow w formularzu 
     * @return
     */
    public function hasErrors()
    {
        if(count($this->_errors)==0)
        {
            return false;
        }
        else
        {
        return true;
        }
    }
    
    public function ignoreError($pole)
    {
        unset($this->_errors[$pole]);
    }

    /**
     * Form::setError()
     * @deprecated 
     * @param mixed $key
     * @param mixed $message
     * @return void
     */
    public function setError($key, $message)
    {
        $this->_errors[$key] = $message;
    }

    /**
     * Form::isSend()
     * Check is form has been submited
     * @return
     */
    public function isSend($submit = "")
    {
        if ($submit == "")
        {
            $submit = $this->submit['name'];
        }
        if (isset($_REQUEST[$submit]) || isset($_POST[$submit]))
        {
            return true;
        } else
        {
            return false;
        }
    }

    /**
     * Form::Get()
     * 
     * @param bool $item
     * @return
     */
    public function Get($item = false)
    {
        if ($item != false)
        {
            if (isset($this->_postData[$item])) //if (isset($_POST[$item]))
            {
                return $this->_postData[$item];
            }
        }
        return '';
    }


    public function getErrors()
    {
        return $this->_errors;
    }


    public function addError($pole, $klucz, $wartosc)
    {
        $this->_errors[$pole][$klucz] = $wartosc;
    }


    /*******************************************************************************************************/
    /**
     * Form::validate()
     * 
     * @param array $source
     * @param array $items - Zawiera liste reguł
     * @param     - required - pole wymagane
     * @param     - min-lenght - minimalna długość
     * @param     - max-lenght -maxymalna długość
     * @param     - reg-value - wartość wyrażenia regularnego
     * @param     - type: 
     * @param             - alpha - może zawierać jedynie znaki alpha
     * @param             - alphanum - może zawierać znaki alphanumeryczne
     * @param             - date - zawiera date 
     * @param             - format - zawiera format daty 
     * @param             - numeric - zawiera jedynie cyfry
     * @param             - bool - zawiera jedynie wartość true lub false
     * @param             - regex - sprawdzanie wartości pola zgodnie z wyrażeniem regularnym :
     * @param             - enum - typ pola enum 
     * @param     - values - tablica wartości które może przyjąć pole formularza
     * @param     - max-selected - maksymalna ilość zaznaczonych pól
     * @param     - error_require - treść błędu, gdy pole jest wymagalne, ale nie jest wypełnione
     * @param     - error_rule  - treść błędu, gdy pole jest niezgodne z rodzajem typu
     * @param     - error_rule_regex - treść błędu używana w sprawdzaniu maila i telefonu kiedy błędna składnia a typ unique_mail lub unique_phone
     *  
     * @return int zwraca ilość błedów
     */
    public function validate($source, $items = array())
    {
        $this->_errors = array();
        $this->_postData = $this->clearVars($_REQUEST);
        foreach ($items as $item => $rules)
        {
            $item = $rules['name'];
            foreach ($rules as $rule => $rule_value)
            {
                if (isset($source[$item]))
                {
                    if(is_array($source[$item]))
                    {
                        $tmp = array();
                        foreach($source[$item] as $s)
                        {
                            $tmp[] = trim($s);
                        }
                        $source[$item] = $tmp;
                    }else{
                        $source[$item] = trim($source[$item]);
                    }
                    //$this->_postData[$item] = $source[$item];
                } else
                {
                    break;
                }
                // SPRAWDZANIE CZY POLE JEST WYMAGANE I CZY USTAWIONA JEST JAKAS WARTOSC
                if (($rule == "required" && $rule_value == true) && ($source[$item] == '' || $source[$item] == null || !isset($source[$item])))
                {

                    if (isset($rules['error_require']))
                    {
                        $this->_errors[$item]['error_require'] = $rules['error_require'];
                    } else
                    {
                        // DOMYŚLNA TREŚĆ BŁĘDU POLA WYMAGANEGO
                        $this->_errors[$item]['error_require'] = "Field required";
                    }
                    break 1;
                }

                // SPRAWDZANIE MIN DŁUGOSCI
                if (isset($rules['min-lenght']) & $rule_value == true & ($rule == "required" | $source[$item] != ""))
                {
                    if (mb_strlen(trim($source[$item]), 'UTF-8') < $rules['min-lenght'])
                    {
                        if(isset($rules['error_min_lenght']))
                        {
                            $this->_errors[$item]['min_lenght'] = $rules['error_min_lenght'];
                        }else{
                            $this->_errors[$item]['min_lenght'] = "Value lenght to short";
                        }
                    }
                }
                // SPRAWDZANIE MAX DŁUGOSCI
                if (isset($rules['max-lenght']) & $rule_value == true & ($rule == "required" | $source[$item] != ""))
                {
                    if (mb_strlen(trim($source[$item]), 'UTF-8') > $rules['max-lenght'])
                    {
                        if(isset($rules['error_max_lenght']))
                        {
                            $this->_errors[$item]['max_lenght'] = $rules['error_max_lenght'];
                        }else{
                            $this->_errors[$item]['max_lenght'] = "Value lenght is to long";
                        }
                        
                    }
                }

                if ($rule == "rule")
                {
                    switch ($rule_value)
                    {
                        default:
                            $this->_errors[$item]['default'] = "Nieznana reguła";
                            break;
                        case "alpha":
                            if ($this->check_alpha($source[$item]) == false && ($rules['required'] == true || $source[$item] != ""))
                            {
                                if (isset($rules['error_rule']))
                                {
                                    $this->_errors[$item]['error_rule'] = $rules['error_rule'];
                                } else
                                {
                                    $this->_errors[$item]['error_rule'] = "Wrong value for rule: alpha";
                                }
                            }
                            break;
                        case "alphanum":
                            if ($this->check_alphanum($source[$item]) == false && ($rules['required'] == true || $source[$item] != ""))
                            {
                                if (isset($rules['error_rule']))
                                {
                                    $this->_errors[$item]['error_rule'] = $rules['error_rule'];
                                } else
                                {
                                    $this->_errors[$item]['error_rule'] = "Wrong value for rule: alphanum";
                                }
                            }
                            break;
                        case "mail":
                            if ($this->check_mail($source[$item]) == false && ($rules['required'] == true || $source[$item] != ""))
                            {
                                $this->_errors[$item]['error_rule'] = "Wrong value for e-mail";
                                break;
                            }

                            break;
                        case "date":
                            $dt = DateTime::createFromFormat($rules['format'], $source[$item]);
                            if (!$dt && ($rules['required'] == true || $source[$item] != ""))
                            {
                                if (isset($rules['error_rule']))
                                {
                                    $this->_errors[$item]['error_rule'] = $rules['error_rule'];
                                } else
                                {
                                    $this->_errors[$item]['error_rule'] = "Wrong value for rule: data " . $rules['format'];
                                }
                                
                                
                            }
                            
                            if(isset($rules['min_date']) && ($rules['required'] == true || $source[$item] != ""))
                            {
                                $md = DateTime::createFromFormat($rules['format'], $rules['min_date']);
                                if($dt < $md)
                                {
                                    if (isset($rules['error_min_date']))
                                    {
                                        $this->_errors[$item]['error_min_date'] = $rules['error_min_date'];
                                    } else
                                    {
                                        $this->_errors[$item]['error_min_date'] = "Wrong min date " . $rules['min_date'];
                                    }
                                    
                                    break;
                                }
                                
                            }
                            if(isset($rules['max_date']) && ($rules['required'] == true || $source[$item] != ""))
                            {
                                $md = DateTime::createFromFormat($rules['format'], $rules['max_date']);
                                if($dt > $md)
                                {
                                    if (isset($rules['error_max_date']))
                                    {
                                        $this->_errors[$item]['error_max_date'] = $rules['error_max_date'];
                                    } else
                                    {
                                        $this->_errors[$item]['error_max_date'] = "Wrong max date" . $rules['max_date'];
                                    }
                                    
                                    break;
                                }
                            }
                            
                            break;
                        case "numeric":
                        if($rules['required'] == true || $source[$item] != "")
                        {
                            if ($this->check_numeric($source[$item]) == false)
                            {
                                if (isset($rules['error_rule']))
                                {
                                    $this->_errors[$item]['error_rule'] = $rules['error_rule'];
                                } else
                                {
                                    $this->_errors[$item]['error_rule'] = "Wrong numeric value";
                                }
                                
                                
                            }else{
                                if(isset($rules['min-value']))
                                {
                                    if($source[$item] < $rules['min-value'])
                                    {
                                        if (isset($rules['error_min_value']))
                                        {
                                            $this->_errors[$item]['error_min_value'] = $rules['error_min_value'];
                                        } else
                                        {
                                            $this->_errors[$item]['error_min_value'] = "Wrong min numeric value ".$rules['min-value'];
                                        }
                                    }
                                }
                                
                                if(isset($rules['max-value']))
                                {
                                    if($source[$item] > $rules['max-value'])
                                    {
                                        if (isset($rules['error_max_value']))
                                        {
                                            $this->_errors[$item]['error_max_value'] = $rules['error_max_value'];
                                        } else
                                        {
                                            $this->_errors[$item]['error_max_value'] = "Wrong max numeric value ".$rules['max-value'];
                                        }
                                    }
                                }
                            }
                        }
                            break;
                        case "bool":
                            if (!isset($source[$item]))
                            {
                                $this->_errors[$item]['error_rule'] = "Wrong bool value";
                            }
                            if ($rules['required'] == true && $source[$item] == '0')
                            {
                                if (isset($rules['error_require']))
                                {
                                    $this->_errors[$item]['error_require'] = $rules['error_require'];
                                } else
                                {
                                    $this->_errors[$item]['error_require'] = "Field required";
                                }
                            }
                            break;
                        case "check":
                            if ($rules['required'] == true && $source[$item] == '0')
                            {                                    
                                if (isset($rules['error_require']))
                                {
                                    $this->_errors[$item]['error_require'] = $rules['error_require'];
                                } else
                                {
                                    $this->_errors[$item]['error_require'] = "Field required";
                                }
                            }                            
                            break;
                        case "regex":
                            if ($this->check_regex($source[$item], $rules['reg-value']) == false && ($rules['required'] == true || $source[$item] != ""))
                            {
                                if (isset($rules['error_rule']))
                                {
                                    $this->_errors[$item]['error_rule'] = $rules['error_rule'];
                                } else
                                {
                                    $this->_errors[$item]['error_rule'] = "Wrong value for rule: regex";
                                }
                            }
                            break;

                        case "float":
                            if ($this->check_float($source[$item]) == false && ($rules['required'] == true || $source[$item] != ""))
                            {
                                if (isset($rules['error_rule']))
                                {
                                    $this->_errors[$item]['error_rule'] = $rules['error_rule'];
                                } else
                                {
                                    $this->_errors[$item]['error_rule'] = "Wrong float value";
                                }
                            }
                            break;
                        case "enum":
                            if (!in_array($source[$item], $rules['values']))
                            {
                                if (isset($rules['error_rule']))
                                {
                                    $this->_errors[$item]['error_rule'] = $rules['error_rule'];
                                } else
                                {
                                    $this->_errors[$item]['error_rule'] = "Wrong enum value";
                                }
                            }
                            break;
                        case "multi":
                            if (!isset($source[$item]))
                            {
                                $this->_errors[$item]['error_rule'] = "Wrong value for rule: multi";
                            } else
                            {
                                if (isset($rules['max-selected']) && count($source[$item]) > $rules['max-selected'])
                                {
                                    $this->_errors[$item]['max'] = "Wrong max multi value" . $rules['max-selected'];
                                }
                                if (isset($rules['min-selected']) && count($source[$item]) < $rules['min-selected'])
                                {
                                    $this->_errors[$item]['min'] = "Wrong min multi value". $rules['min-selected'];
                                }
                            }
                            break;
                        case "pass":
                            if ($this->check_pass($source[$item]) == false)
                            {
                                if (isset($rules['error_rule']))
                                {
                                    $this->_errors[$item]['error_rule'] = $rules['error_rule'];
                                } else
                                {
                                    $this->_errors[$item]['error_rule'] = "Wrong value for rule password";
                                }
                            }
                            break;
                        case "phone":
                            if ($this->check_phone($source[$item]) == false && ($rules['required'] == true || $source[$item] != ""))
                            {
                                if (isset($rules['error_rule']))
                                {
                                    $this->_errors[$item]['error_rule'] = $rules['error_rule'];
                                } else
                                {
                                    $this->_errors[$item]['error_rule'] = "Wrong value for rule phone";
                                }
                            }
                            break;
                        case "text":
                            if ($this->check_text($source[$item]) == false && ($rules['required'] == true || $source[$item] != ""))
                            {
                                if (isset($rules['error_rule']))
                                {
                                    $this->_errors[$item]['error_rule'] = $rules['error_rule'];
                                } else
                                {
                                    $this->_errors[$item]['error_rule'] = "Wrong value for rule text";
                                }
                            }
                            break;
                        case "url":
                            if ($this->check_url($source[$item]) == false && ($rules['required'] == true || $source[$item] != ""))
                            {
                                if (isset($rules['error_rule']))
                                {
                                    $this->_errors[$item]['error_rule'] = $rules['error_rule'];
                                } else
                                {
                                    $this->_errors[$item]['error_rule'] = "Wrong url";
                                }
                            }                            
                        break;
                    }
                }
            }
        }
        if(count($this->_errors)>0) {
            return $this->_errors;
        }
    }
    /*******************************************************************************************************/

    private function check_url($wartosc)
    {
        if (!preg_match("/^[a-z](?:[-a-z0-9\+\.])*:(?:\/\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&'\(\)\*\+,;=:])*@)?(?:\[(?:(?:(?:[0-9a-f]{1,4}:){6}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|::(?:[0-9a-f]{1,4}:){5}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){4}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:[0-9a-f]{1,4}:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){3}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){2}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4}|(?:(?:[0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::)|v[0-9a-f]+[-a-z0-9\._~!\$&'\(\)\*\+,;=:]+)\]|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}|(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&'\(\)\*\+,;=@])*)(?::[0-9]*)?(?:\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&'\(\)\*\+,;=:@]))*)*|\/(?:(?:(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&'\(\)\*\+,;=:@]))+)(?:\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&'\(\)\*\+,;=:@]))*)*)?|(?:(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&'\(\)\*\+,;=:@]))+)(?:\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&'\(\)\*\+,;=:@]))*)*|(?!(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&'\(\)\*\+,;=:@])))(?:\?(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&'\(\)\*\+,;=:@])|[\x{E000}-\x{F8FF}\x{F0000}-\x{FFFFD}\x{100000}-\x{10FFFD}\/\?])*)?(?:\#(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&'\(\)\*\+,;=:@])|[\/\?])*)?$/i", $wartosc))
        {
            return 0;
        } else
        {
            return 1;
        }

    }


    private function check_phone($wartosc)
    {
        $w = '' . preg_replace('/[^0-9]+/', '', $wartosc);
        if (!preg_match("#^\+?(([0-9]|\\-|\\(|\\)|[[:space:]]){9,30})$#i", $wartosc) | mb_strlen($w) < 9)
        {
            return 0;
        } else
        {
            return 1;
        }

    }

    private function check_float($wartosc)
    {
        if (!preg_match("/^[0-9]+(.[0-9]+)?$/", $wartosc))
        { 
            return 0;
        } else
        {
            return 1;
        }

    }

    private function check_pass($wartosc)
    {
        if (preg_match("/[A-Za-z-0-9\!\@\#\$\%\^\&\*]/", $wartosc))
        {
            if (preg_match('/[A-Z]/', $wartosc) && preg_match('/[0-9]/', $wartosc))
            {
                return 1;
            } else  return 0;

        } else  return 0;
    }


    private function check_min_lenght($zmienna, $wartosc) // Zwraca treść błędu gdy zmienna za krótka
    {
        if (mb_strlen($zmienna) < $wartosc) return "Wartośc pola jest zbyt króka";
    }

    private function check_max_lenght($zmienna, $wartosc) // Zwraca treść błedu gdy zmienna za długa
    {
        if (mb_strlen($zmienna) > $wartosc) return "Wartośc pola jest zbyt długa";
    }

    /**
     * Form::check_alpha()
     * 
     * @param string $nazwa - zawiera nazwę pola 
     * @param string $wartosc -  wartość pola
     * @param array $rules - tablica zasad które musi spełnić dane pole
     * @return integer 0 - błąd , 1 - bez błędu
     */
    private function check_alpha($wartosc)
    {
        //preg_match('/[ 1234567890\'\"<>,.!?\r\t_=+~`!@#$%^&;]/', $wartosc)
        if (preg_match('/[\'\"]/', $wartosc))
        {
            // BŁĄD
            return 0;
        } else
        {
            return 1;
        }
    }

    private function check_text($wartosc)
    {
        if (preg_match('[^0-9A-Za-z]', $wartosc))
        {
            // BŁĄD
            return 0;
        } else
        {
            return 1;
        }
    }

    private function check_mail($wartosc)
    {
        if (!preg_match('#^[a-zA-Z0-9]+[_a-zA-Z0-9-]*(\.[_a-z0-9-]+)*@[a-z0-9]+(-[a-z0-9]+)*(\.[a-z0-9-]+)*(\.[a-z]{2,6})$#i', $wartosc))
        {
            // BŁĄD
            return 0;
        } else
        {
            return 1;
        }
    }


    private function check_alphanum($wartosc)
    {
        if (preg_match('/[\'\"<>,.!?\r\t=+~`!@#$%^&;]/', $wartosc)) // USUNIETY ZNAK _ 
        {
            // BŁĄD
            return 0;
        } else
        {
            return 1;
        }
    }

    private function check_numeric($wartosc)
    {
        if ($wartosc != "")
        {
            if (!is_numeric($wartosc))
            {
                return 0;
                //$this->_errors[$nazwa] = "Dozwolone tylko cyfry";
            } else
            {
                return 1;
            }
        }

    }

    private function check_regex($wartosc, $preg)
    {
        if (!preg_match($preg, $wartosc))
        {
            return 0;
            // BŁĄD
        } else
        {
            return 1;
        }
    }



}

?>
