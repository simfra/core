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
    private $modifiers = array("int", "string");
    private $url = null;

    public function __construct(\Core\Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->page_struct = $this->getConfig($this->kernel->getApplicationPath());
    }


    public function getConfig($application_path)
    {
        //echo __DIR__. "/../../" . "App/Struct/struct.php";
        if (file_exists($application_path ."Struct/Struct.php")) {
            include $application_path ."Struct/Struct.php";
            if (isset($page) || count($page) == 0) {
                return $page;
            } else {
                throw new FatalException("Route", "Broken page array in Struct.php");
            }
        } else {

            throw new FatalException("Route","Struct file not found");
        }
    }


    /**
     * Route::splitModifiers()
     * Splits a part of url if contains modifier, parametr
     * @param mixed $part - part of url to be split
     * @return array with modifier, parameter and name
     */
    public function splitModifiers($part)
    {
        $ret = array();
        $ret['modifier'] = "";
        $ret['parametr'] = "";
        $ret['name'] = "";
        $t1 = explode("|", $part);
        if (count($t1) > 0) { // sa jakies modyfikatory
            // echo "Jest modyfikator: ".$t1[1]."<br />";
            $ret['name'] = $t1[0];
            if (isset($t1[1])) {
                $temp = explode(":", $t1[1]);
            }

            if (isset($temp[0]) && in_array($temp[0], $this->modifiers)) {// znany jest modyfikator
                $ret['modifier'] = $temp[0];
                if (isset($temp[1])) { // jest parametr do modyfikatora
                    //$parametr = $temp[1];
                    $ret['parametr'] = $temp[1];
                } else {
                    $ret['parametr'] = "";
                }
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
    private function getStringBetween($string, $start, $end, $back = false)
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


//    public function getLastID()
//    {
//        $id = -1;
//        if (isset($this->page_struct) && count($this->page_struct)>0)
//        {
//            foreach ($this->page_struct as $key => $value)
//            {
//                if ($value['id'] > $id)
//                {
//                    $id = $value['id'];
//                }
//            }
//        }
//        return $id;
//    }    
//    
//    


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
                $t1 = $this->getStringBetween($v1, "[", "]");
                if ($t1 === "") {
                    // part of url doesn't contain any modifier'
                    $ret .= "\/\b" . $v1 . "\b";
                } else {
                    // has modifier
                    $mod = $this->splitModifiers($t1);
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
        return $ret;
    }


    /**
     * Route::extractPage()
     * Extracts first element from array of urls (from method checkUrl)
     * @param mixed $urls - list of urls
     * @param string $type - desribes type of element should be returned
     * @return mixed - if contains this type of url or false if not
     */
    private function extractPage($urls, $type = "exact")
    {
        foreach ($urls as $page) {
            if (($type == "exact" && trim($page['exact']) != "") || ($type == "cond" && trim($page['cond']) != "")) {
                //die("1");
//                print_r($this->page_struct[$page['id']]);
                $strona['struct'] = new AppArray($this->page_struct[$page['id']]);
                $strona['lang'] = reset($page['lang']); // first language of selected page
                $strona['url'] = ($page['url']) ? $page['url'] : "1111";//$this->url;
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
    public function checkURL(Request $request)
    {
        $url = $request->query->url;
        $this->url = $url;
        $links = array();
        $url = "/" . trim($url, "/");

        foreach ($this->page_struct as $key => $value) {
            $temp = array();
            foreach ($value['url'] as $key_lang => $lang) {
                if ($lang !== $url) {
                    $wynik = preg_grep("/^" . $this->replaceUrl($lang) . "$/iu", array(0 => $url));
                    if (count($wynik) > 0) {
                        $temp['id'] = $key;
                        $temp['url'] = $request->query->url;
                        $temp2 = $this->compareLinks($url, $lang);
                        $temp['exact'] = $temp2['exact'];
                        $temp['lang'][] = $key_lang;
                        $temp['cond'] = $temp2['cond'];
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

        // sprawdzanie ile jest dokladnych linkow a ile warunkowych
        $ret = array();
        $exact = count(array_filter(array_column($links, "exact")));
        $cond = count(array_filter(array_column($links, "cond")));
        if ($exact == 1 && $cond <= 1) {
            return $this->extractPage($links, "exact");
        } elseif ($exact == 0 && $cond > 0) {
            return $this->extractPage($links, "cond");
        } elseif ($exact > 1 || $cond > 1) {
            // temporary - to show i
            throw new HttpException(404, "Requested url fit to more than one url in struct!. Check Your struct file.");
        } else {
            throw new NotFoundException(404, "Requested url not found.");
        }
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
                if ($link2['parametr'] == "?") {
                    $exact = 0;
                }
                if ($link2['parametr'] != "") {
                    $parametr = ":" . $link2['parametr'];
                } else {
                    $parametr = "";
                }
                if ($link2['modifier'] == "int") {
                    if (isset($url[$key]) && ctype_digit($url[$key])) {
                        $temp[] = "[" . $link2['name'] . "|int$parametr]";
                    } elseif (($link2['parametr'] == "?" && !isset($url[$key]))) {
                        $temp[] = "[" . $link2['name'] . "|int$parametr]";
                    } else {
                        $temp[] = $url[$key];
                    }
                } elseif ($link2['modifier'] == "string") {
                    if (is_string($url[$key])) {
                        $temp[] = "[" . $link2['name'] . "|string$parametr]";
                    } else {
                        $temp[] = $url[$key];
                    }
                } else {
                    $temp[] = $url[$key];
                }
            }
        }
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