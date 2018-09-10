<?php
namespace Core\Config;

use Core\Bundle;

class Config extends Bundle
{
    private $config = null;
    
    public function __construct()
    {

        //echo "construct";
//        if($settings != "") {
//            $this->_config = $settings;
//        }
        //$this->loadConfigFromIni();
        return $this;
    }



    public function get($variable)
    {
        if (isset($this->config[$variable])) {
            return $this->config[$variable];
        }
    }

    public function getConfig($applicationPath, $application_name)
    {
        //($this->config) ? return $this->config : return
        if ($this->config!=null) {
            return $this->config;
        } else {
            return $this->loadConfigFromIni($applicationPath, $application_name);
        }
    }


    public function loadConfigFromIni($applicationPath, $application_name)
    {
       // echo $_SERVER['DOCUMENT_ROOT'] . "<br />\n";//
        //echo $this->getKernel()->getApplicationPath();
        //echo "<pre>";
        //print_r(debug_backtrace());
        //echo "</pre>";
        define("APP_NAME", $application_name);
        //define("APP_NAMESPACE")
        echo getcwd();
        //echo "@#@".$this->getRootDir();
        $root = realpath(__DIR__  . "/../../../") . "/";
        define("ROOT_DIR", $root);
        define("APP_DIR", realpath($root  . "App/$application_name")."/");


        echo "<pre>";
        //print_r(debug_backtrace());

        //print_r(get_defined_constants(true)['user']);
        echo "</pre>";
        //echo "####".$applicationPath ."######";
        $plik = $applicationPath ."Config/Config.php";
        //echo "#".realpath($_SERVER['DOCUMENT_ROOT'] ."/../App/$application_name/Config/config.php")."<br>";
        //echo $_SERVER['DOCUMENT_ROOT'] ."/../web/App/$application_name/Config/config.php<br>";
        if (file_exists($plik)) {
            if (is_readable($plik)) {
                //file
                //print_r( $output = shell_exec('php -l "'.$plik.'"'));
                //@TODO zrobic bezpieczne wczytywanie plikow. http://www.php.net/manual/en/function.php-check-syntax.php
                $t = include($plik);
                //$t = file_get_contents($plik);
                //$this->_config[] = parse_ini_file($plik, true, INI_SCANNER_TYPED);
//echo "<pre>iiii";
//print_r($t);
//echo "</pre>";
                if (is_array($t)) {
                    return $this->config = $t;
                }
//                echo "<pre>111";
//                print_r($this->_config);//['bundles']);//json_decode($t, true));
//                echo "</pre>";
            } else {
                die("File: ".$plik . " isn't readable");
            }
        } else {
            die("File: ".$plik . " doesn't exist");
        }
        return;
//        
//        foreach($file as $line)
//        {
//            if(!preg_match('/^\/|^\/\/|^#|^\ /i', $line)) // Skip lines with single /, double // or # - comments
//            {
//                if(trim($line)!="") {
//                    $temp = explode("=",$line);
//                    if(count($temp)==2)
//                    {
//                        $this->_config[trim($temp[0])] = eval(trim($temp[1]));
//                        //echo "<pre>";
//                        //print_r($temp);
//                        //echo "</pre>";
//                    }
//                    else{
//                        echo "bledna wartosc " .$line . "<br />";
//                    }
//                }
//                //echo $line."<br />";
//            }
//        }        
//        //echo "<pre>";
        //print_r($this->_config);
        //echo "</pre>";
    }
}