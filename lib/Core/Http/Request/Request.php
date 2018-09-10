<?php
namespace Core\Http\Request;

use Core\Objects\AppArray;
use Core\Objects\AppObject;

class Request
{
  
    public $languages = array();
    public $query;
    public $cookie = array();


    
    public static function Create()
    {

        //print_r(dirname($_SERVER['SCRIPT_FILENAME']));
//        die();
        $new = new static();
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }
        //echo PHP_VERSION_ID;
        if (PHP_VERSION_ID>=70000) {
            //echo "php7";
        }
        $url = urldecode($_SERVER['REQUEST_URI']);
        $query = array();
        $query['url'] = "/".trim(parse_url($url, PHP_URL_PATH), "/");
        $t['query'] = parse_url($url, PHP_URL_QUERY);
        parse_str($t['query'], $t['query']);
        $query['args'] = new AppObject(array("GET"=>new AppArray($t['query']), "POST"=> new AppArray($_POST)));
        //$query['args']->GET = new App_Array($t['query']);
        //['get'] = new App_Array($t['query']);
        $query['method'] = $_SERVER['REQUEST_METHOD'];
        // echo "<pre>";
        // print_r($_POST);
        // echo "</pre>";
        $new->query = new AppObject($query);
        //$new->query->url = new App_Array($temp['url']);
        //$new->query->query = new App_Array($temp['url']);
        //$new->_method = $_SERVER['REQUEST_METHOD'];
        $new->cookie = new AppArray($_COOKIE);
        $new->languages = self::getLanguagesFromUser();
        return $new;
    }
    
    
    
    
    /**
     * request::getMethod()
     * Get request method
     * @return string - GET, POST etc
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    
    public function getCookie($param = "")
    {
        if (isset($this->cookie[$param])) {
            return $this->cookie[$param];
        }
    }
    
    /**
     * request::getLanguages()
     * Get list of languages from user browser
     * @return array of languages
     */
    public function getLanguages()
    {
        return $this->languages;
    }
    
    /**
     * request::getPreferredLanguage()
     * Get name of preferred language matched to list, if not matched return first language
     * @param string $list if empty return first language, else name of preferred language first occured in array
     * @return string name of language
     */
    public function getPreferredLanguage($list = "")
    {
        foreach ($this->languages as $lang => $value) {
            foreach ($list as $key_list => $value_list) {
                if (preg_match("/" . $lang . "(-[\w]{1,2})?/i", $value_list)) {
//                    echo "znaleziony to $value_list";
                    return $value_list;
                }
            }
        }
        return "";
        /*
        print_r($list);
        if (is_array($list)) {
            foreach ($list as $key => $value) {
                echo $key . "<br />";
                if (isset($this->languages[$key])) {
                    echo $key;
                    return $value;
                }
            }
        } else {
            if (isset($this->languages[$list])) {
                return $list;
            }
        }*/
        //return current(array_keys($this->languages));
    }
    
    /**
     * request::getLanguagesFromUser()
     * Get list of prefered languages set in user browser
     * @return array
     */
    private static function getLanguagesFromUser()
    {
        $langs = array();
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $parsed_languages);
            if (count($parsed_languages[1])) {
                $langs = array_combine($parsed_languages[1], $parsed_languages[4]);
                // set default 1 for any without q factor
                foreach ($langs as $lang => $val) {
                    if ($val === '') {
                        $langs[$lang] = 1;
                    }
                }
            }
            arsort($langs, SORT_NUMERIC);
        }

        return $langs;
    }

}