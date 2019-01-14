<?php
namespace Core\Config;

use Core\Bundle;

class Config extends Bundle
{
    private $config = null;
    
    public function __construct()
    {
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
        define("APP_NAME", $application_name);
        //echo getcwd();
        $root = realpath(__DIR__  . "/../../../") . "/";
        define("ROOT_DIR", $root);
        define("APP_DIR", realpath($root  . "App/$application_name")."/");
        $plik = $applicationPath ."Config/Config.php";
        //echo "#".realpath($_SERVER['DOCUMENT_ROOT'] ."/../App/$application_name/Config/config.php")."<br>";
        //echo $_SERVER['DOCUMENT_ROOT'] ."/../web/App/$application_name/Config/config.php<br>";
        if (file_exists($plik)) {
            if (is_readable($plik)) {
                //file
                //print_r( $output = shell_exec('php -l "'.$plik.'"'));
                //@TODO zrobic bezpieczne wczytywanie plikow. http://www.php.net/manual/en/function.php-check-syntax.php
                $t = include($plik);
                if (is_array($t)) {
                    return $this->config = $t;
                }
            } else {
                die("File: ".$plik . " isn't readable");
            }
        } else {
            die("File: ".$plik . " doesn't exist");
        }
        return;
    }
}