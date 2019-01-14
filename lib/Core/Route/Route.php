<?php
namespace Core\Route;

use Core\Exception\FatalException;
use Core\Exception\HttpException;
use Core\Exception\NotFoundException;
use Core\Http\Request\Request;
use Core\Objects\AppArray;
use Core\Objects\AppObject;

/**
 * Route
 * Routing class
 * @package
 * @author Pawel Synarecki
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class Route
{
    protected $page_struct = null;
    private $kernel = null;
    private static $modifiers = array("int", "string");
    private $url = null;

    public function __construct(\Core\Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->page_struct = $this->getConfig($this->kernel->getApplicationPath());
        //echo "<pre>";
        //print_r($this->page_struct);
        //echo "</pre>";
    }

    /**
     * @param $page_permissions string of comma separated permissions that user must have to access the page. If empty array than page is accesible for all
     * @param $user_permissions array of user permissions from database
     * @return bool true if page_permisssions == empty or if array of user_permissions contains at least one from page_ermissions
     */
    public static function checkPermissions($page, $user_permissions)
    {
        if (!is_array($page)) {
            $page = explode(",", $page);
        }
        if (count(array_filter($page)) > 0) {
            $page_permissions = array_filter($page);//explode(",", trim($page['permissions']));
        } else {
            //echo "uprawnienia strony puste " . $page['name'] ."<br/>";
            return true;
        }

        if (!is_array($user_permissions)) {
            //echo "nie tablica";
            $user_permissions = explode(",", $user_permissions);
        }
        if (empty($page_permissions) || count($page_permissions) == 0) {
            return true;
        }
        foreach ($page_permissions as $permission) {
            if ( (trim($permission) != "" && in_array(trim($permission), $user_permissions)) || in_array("admin", $user_permissions) ) {
                //echo "uprawnienia $permission są! (". print_r($user_permissions) .")";
                return true;
            } else {
                trigger_error("Permission '".trim($permission)."' required to access this page is not in user set", E_USER_WARNING);
                return false;
            }
        }
        return false;
    }

    public static function getConfig($application_path)
    {
        //echo __DIR__. "/../../" . "App/Struct/struct.php";
        if (file_exists($application_path ."Struct/struct.php")) {
            include $application_path ."Struct/struct.php";
            if (!isset($page)) {
                throw new FatalException("Route", "Broken page array in Struct.php");
            }
        } else {
            $page = null;
        }

        if (file_exists($application_path ."Struct/struct_cache.php")) {
            include $application_path ."Struct/struct_cache.php";
            if (!isset($page_cache)) {
               // throw new FatalException("Route", "Broken page array in struct_cache.php");
                $page_cache = null;
            }
        } else {
            $page_cache = null;
        }

        if ($page !== null && $page_cache !== null ) {
            // merge both struct files
            $temp = [];
            foreach($page as $key=>$value) {
                $page_cache[] = $value;
            }
            return $page;
            return array_merge($page, $page_cache);
        } elseif ($page === null && $page_cache === null) {
            throw new FatalException("Route","Struct file not found");
        } elseif ($page !== null && $page_cache == null){
            return $page;
        }
/*
        if (file_exists($application_path ."Struct/Struct.php")) {
            include $application_path ."Struct/Struct.php";
            if (isset($page) || count($page) == 0) {
                return $page;
            } else {

            }
        } else {

            throw new FatalException("Route","Struct file not found");
        }*/
    }


    /**
     * Route::splitModifiers()
     * Splits a part of url if contains modifier, parametr
     * @param mixed $part - part of url to be split
     * @return array with modifier, parameter and name
     */
    public static function splitModifiers($part)
    {
        $ret = array();
        $ret['modifier'] = "";
        $ret['parametr'] = "";
        //$ret['default'] = "";
        $ret['name'] = "";
        $t1 = explode("|", $part);
        //print_r($t1);
        if (count($t1) > 0) { // sa jakies modyfikatory
            // echo "Jest modyfikator: ".$t1[1]."<br />";
            $ret['name'] = $t1[0];
            if (isset($t1[1])) {
                $temp = explode(":", $t1[1]);
            }

            if (isset($temp[0]) && in_array($temp[0], self::$modifiers)) {// znany jest modyfikator
                $ret['modifier'] = $temp[0];
                if (isset($temp[1])) { // jest parametr do modyfikatora
                    //$parametr = $temp[1];
                    $ret['parametr'] = $temp[1];
                } else {
                    $ret['parametr'] = "";
                }
            }
            if (isset($t1[2])) { // default value
                $ret['default'] = $t1[2];
            }
        } else {
            $ret['name'] = $part;
        }
        return $ret;
    }

    /**
     * Route::getStringBetween()
     * Simple method to get string from between to characters/strings/tags.
     * @param mixed $string - source string to be parsed
     * @param mixed $start - character/string as a starting point of cut (not included in returned part)
     * @param mixed $end - character/string as a ending point of cut (not included in returned part)
     * @param bool $back - default=false, if true and starting point is in source string, will return source string, if false will return empty string
     * @return mixed $back param - will return empty string or cutted string
     */
    public static function getStringBetween($string, $start, $end, $back = false)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            if ($back === false) {
                return '';
            } else {
                return $string;
            }
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }


    /**
     * Route::replaceUrl()
     * Replace modifiers/params with equal regex expressions - needed in regex comparison of 2 urls
     * @param mixed $url - url to be change
     * @return string - modified url
     */
    private function replaceUrl($url)
    {
        $url = explode("/", $url);
        $ret = "";
        foreach ($url as $key => $v1) {
            if ($v1 != "") {
                $t1 = self::getStringBetween($v1, "[", "]");
                if ($t1 === "") {
                    // part of url doesn't contain any modifier'
                    $ret .= "\/\b" . $v1 . "\b";
                } else {
                    // has modifier
                    $mod = self::splitModifiers($t1);
                    if ($mod['modifier'] === "int") {
                        if ($mod['parametr'] === "?") {
                            $ret .= "(\/\d+)?";
                        } else {
                            $ret .= "\/(\d+)";
                        }
                    }
                    if ($mod['modifier'] === "string") {
                        if (ctype_digit($mod['parametr']) && $mod['parametr'] > 0) {
                            $ret .= "\/([a-zA-Z\-]|[\w]){0," . $mod['parametr'] . "}?";
                        } else {
                            $ret .= "\/([a-zA-Z\-]|[\w])+";
                        }
                    }
                }
            }
        }
        //echo "replaceURL $ret<br />";
        return $ret;
    }


    /**
     * Route::extractPage()
     * Extracts first element from array of urls (from method checkUrl)
     * @param mixed $urls - list of urls
     * @param string $type - desribes type of element should be returned
     * @return mixed - if contains this type of url or false if not
     */
    private function extractPage($urls, $type = "exact", $preffered_lang = "")
    {
        /*echo "<pre>URL";
        print_r($urls);
        echo "</pre>";*/
        foreach ($urls as $page) {
            if (($type == "exact" && trim($page['exact']) != "") || ($type == "cond" && trim($page['cond']) != "")) {
                $strona['struct'] = new AppArray($this->page_struct[$page['id']]);
                if ($preffered_lang != "" && in_array($preffered_lang, $page['lang'])) {
                    $strona['lang'] = $preffered_lang; // setting up page language same as preffered language from user browser
                } else {
                    $strona['lang'] = reset($page['lang']); // first language of selected page
                }
                $strona['url'] = ($page['url']) ? $page['url'] : "";//$this->url;
                if ($type == "exact") {
                    $url_struct = $page['exact'];
                } else {
                    $url_struct = $page['cond'];
                }
                //echo "URL : $this->url " . $url_struct;
                $strona['params'] = new AppArray($this->extractParams( $url_struct, $page['url']));
                /*echo "<pre>PARAMS";
                print_r($strona['params']);
                echo "</pre>";*/
                return new AppObject($strona);
            }
        }
        return false;
    }


    /**
     * Route::checkURL()
     * Checks if requested url is valid and exists in page structure
     * @param mixed $request - object \Core\Http\Request\Request
     * @return array of matched page if found, or false if not. Throw Exception when 2 or more url matched requested url
     */
    public function checkURL(Request $request, $preffered_lang)
    {
        $url = $request->query->url;
        $this->url = $url;
        $links = array();
        $url = "/" . trim($url, "/");
        foreach ($this->page_struct as $key => $value) {
            $temp = array();
            foreach ($value['url'] as $key_lang => $lang) {
                if (strpos($url, "[" ) === false) {
                    if ($lang !== $url) {
                        //echo "<br/> $lang $url /^" . $this->replaceUrl($lang) . "$/iu<br />";
                        $wynik = preg_grep("/^" . $this->replaceUrl($lang) . "$/iu", array(0 => $url));
                        if (count($wynik) > 0) {
                            $temp['id'] = $key;
                            $temp2 = $this->compareLinks($url, $lang);
                            ///echo "<br />aaaa".var_dump($temp2);
                            $temp['url'] = $temp2['link'];//$request->query->url;
                            $temp['exact'] = "";//$temp2['exact'];
                            $temp['lang'][] = $key_lang;
                            $temp['cond'] = $temp2['exact'];
                            $links[$key] = $temp;
                        }
                    } else {
                        // dokladnie dopasowany
                        $temp['id'] = $key;
                        $temp['url'] = $request->query->url;
                        $temp['exact'] = $url;
                        $temp['lang'][] = $key_lang;
                        $temp['cond'] = "";
                        $links[$key] = $temp;
                    }
                }
            }
        }
        // sprawdzanie ile jest dokladnych linkow a ile warunkowych
        $ret = array();
        $exact = count(array_filter(array_column($links, "exact")));
        $cond = count(array_filter(array_column($links, "cond")));
        if ($exact == 1 && $cond <= 1) {
            return $this->extractPage($links, "exact", $preffered_lang);
        } elseif ($exact == 0 && $cond > 0) {
            return $this->extractPage($links, "cond", $preffered_lang);
        } elseif ($exact > 1 || $cond > 1) {
            // temporary - to show i
            //echo "<pre>LINKS";
            //print_r($links);
            //echo "</pre>";
            throw new HttpException( "Requested url fit to more than one url in struct!. Check Your struct file.", 404);
        } else {
            throw new NotFoundException("Requested url not found.", 404);
        }
    }

    public function extractParams($url, $url_struct)
    {
        $return = [];
        if (strpos($url_struct, "[" ) === false || strpos($url_struct, "]" ) === false) {
            //echo "extract params return false $url     $url_struct";
            return $return;
        }
        $url = explode("/", trim($url, "/"));
        $url_struct = explode("/", trim($url_struct, "/"));
        foreach ($url_struct as $key => $part) {
            $link2 = $this->splitModifiers($this->getStringBetween($part, "[", "]", true));
            if (in_array($link2['modifier'], self::$modifiers)) {
                $return[$link2['name']] = (isset($url[$key])) ? $url[$key] : "";
            }
        }
        return $return;
    }

    /**
     * Route::compareLinks()
     * Compares 2 urls - one from request and second from page struct
     * @param mixed $url - url from request
     * @param mixed $url_struct - url from struct
     * @return array with page info when urls matched or false if not
     */
    public function compareLinks($url, $url_struct)
    {
        //echo "<br />URL: $url   URL_STRUCT: $url_struct <br />";
       // echo "DUPA". var_dump(trim($url, "/")) . " " . trim("/", $url_struct);
        if (trim($url,"/") === trim($url_struct,"/")) {
            //echo "dokladnie dopasowany";
            $ret['exact'] = $url;//implode("/", $url);
            $ret['cond'] = [];
            $ret['link'] = $url_struct;//implode("/", $url_struct);
            return $ret;
        }
        $url = explode("/", trim($url, "/"));
        $url_struct = explode("/", trim($url_struct, "/"));
        $ret = array();
        $check_exact = array();
        $check_cond = array();
        $exact = 1;
        $temp = array();
        foreach ($url_struct as $key => $link) {
            if ($link != "") {
                $link2 = $this->splitModifiers($this->getStringBetween($link, "[", "]", true));
                //echo "<pre>*&****";
                //print_r($link2);
                //echo "</pre>";
                if ($link2['parametr'] == "?") {
                    $exact = 0;
                }
                if ($link2['parametr'] != "") {
                    $parametr = ":" . $link2['parametr'];
                } else {
                    $parametr = "";
                }
                if (isset($link2['default']) && $link2['default']!="") {
                    $default = "|" . $link2['default'];
                } else {
                    $default = "";
                }
                if ($link2['modifier'] == "int") {
                    if (isset($url[$key]) && ctype_digit($url[$key])) {
                        $temp[] = "[" . $link2['name'] . "|int$parametr$default]";
                    } elseif (($link2['parametr'] == "?" && !isset($url[$key]))) {
                        $temp[] = "[" . $link2['name'] . "|int$parametr$default]";
                    } else {
                        $temp[] = $url[$key];
                    }
                } elseif ($link2['modifier'] == "string") {
                    if (is_string($url[$key])) {
                        $temp[] = "[" . $link2['name'] . "|string$parametr$default]";
                    } else {
                        $temp[] = $url[$key];
                    }
                } else {
                    $temp[] = $url[$key];
                }

            } else {
                die( "co to ");
            }
        }
        //echo "<pre>";
        //print_r($temp);
        //echo "</pre>";
        if (implode("/", $temp) !== implode("/", $url_struct)) {
            return false;
        }
        if ($exact == 0) {
            $check_cond = $temp;
        } else {
            $check_exact = $temp;
        }
        //echo "<br />Link do sprawdzania: ".implode("/",$url).". Link wynikowy: ". implode("/", $check). " Link warunkowy: ".implode("/", $check_warunkowy);
        $ret['exact'] = implode("/", $check_exact);
        $ret['cond'] = implode("/", $check_cond);
        $ret['link'] = implode("/", $url_struct);
        return $ret;
    }


}