<?php
namespace Core\Debug;

use Core\Objects\App_Array;
use Core\Objects\App_Object;
use Core\Bundle;

class Debug extends Bundle
{
    private $debug = [];
    public $database = ["time" => 0];
    public $show_buffer = false;
    const SESSION_NAME = "SIMFRA_DEBUG";
    private $theme = "default";
    public $minimalized_toolbar = true;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $this->debug = ["notice"=>[], "warning"=>[]];
        set_error_handler(array($this, "errorException"));
        register_shutdown_function([$this, "shutDown"]);
        $this->show_buffer = true;
    }

    public function addDatabaseLog($log)
    {
        $this->database['queries'][] = $log;
    }


    public function addDebugError($type, $error)
    {
        if ($type == "notice" || $type == "warning" || $type == "database")  {
            $this->debug[$type][] = $error;
        }
    }
    
    public function makeDevToolbar($content, $show_buffer = true)
    {
        if ($this->isBundle("View")) {
            $tpl = $this->getBundle("View");
            // testowo
            $t = ["multi"=>["jeden" => ["poziom1"=> 1, "poziom2" => 2], "dwa" => [0=> "aaa", 1 => '<input type="text" value="kuku"/>'] ]];
            $tpl->assign("minimalized_toolbar", $this->minimalized_toolbar);
            $tpl->assign("dev_templates", array_merge($tpl->get_template_vars(), $t, ["struct" => $this->getKernel()->page->struct->getAll()]));//$this->parseTemplate(array_merge($tpl->get_template_vars(), , $this->getKernel()->page->struct->getAll(), $t)));
            if ($this->show_buffer == true && mb_strlen(ob_get_contents()) && $show_buffer === true) {
                $tpl->assign("debug_buffer", ob_get_contents());
            }
            if (strlen(ob_get_contents())) {
                ob_end_clean();
            }
            // user
            if ($this->isBundle("Session")) {
                $session = $this->getBundle("Session");
                $user = $session->getUser();
                $user['permissions'] = explode(",", $session->getPermissions());
                $tpl->assign("logged_user", $user);
            }
            $tpl->assign("dev", $this->devToolbar());
            $toolbar = $tpl->fetch("Debug/toolbar.tpl");
            $content = trim($content);
            $theme_css = '<link type="text/css" href="/themes/toolbar/'.$this->theme.'/toolbar.css" rel="stylesheet" />';
            $theme_js = '<script src="/themes/toolbar/'.$this->theme.'/toolbar.js" ></script>';
            (strpos($content, "<body")=== false && strpos($content, "</head>") == false) ? $content = "<body>$content</body>" : "";
            (strpos($content, "</head>") === false)
                ? $content = '<head>' . $theme_css . $theme_js . '</head>' . $content
                : $content = str_replace("</head>", $theme_css . $theme_js . '</head>', $content);
            (strpos($content, "<html")=== false) ? $content = "<html>$content</html>" : "";
            (strtoupper(substr($content, 0, 9)) !== '<!DOCTYPE') ? $content = "<!DOCTYPE html>$content"  : "";
            if (strpos($content, "</body>")!== false) {
                $content = str_replace("</body>", $toolbar . "</body>", $content);
            } else {
                $content .= $toolbar;
            }
        }
        $content .="";
        return $content;
    }
    

    private function devToolbar()
    {
        $mem = memory_get_usage(false);
        (!@session_id() ? $temp['session']=false : $temp['session'] = @session_id());
        $kernel = $this->getKernel();
        $temp['memory'] = round($mem/1024);
        $temp['files'] = get_included_files();
        $temp['lang'] = $kernel->page->preferred_lang;
        $temp['database'] = $this->database;
        $temp['page'] = [
                "controller" => !empty($kernel->page) ? $kernel->page->struct->controller : "[EMPTY]",
                "method" => !empty($kernel->page) ? $kernel->page->struct->method : "[EMPTY]",
                "route"=> !empty($kernel->page->url) ? $kernel->page->url : $_SERVER['REQUEST_URI'],
                "id" =>  !empty($kernel->page) ? $kernel->page->id : "[EMPTY]",
                "app" => !empty($kernel->application_name) ? $kernel->application_name : "NO APP NAME"
            ];
            $temp['class_path'] = class_exists(APP_NAME . "\\Controller\\" . $kernel->page->struct->controller, false)
                ? (new \ReflectionClass(APP_NAME."\Controller\\" . $kernel->page->struct->controller))->getName()  :  $kernel->page->struct->controller;
            $temp['http'] = http_response_code();
            $temp['time'] = round(microtime(true) - $kernel->start_time, 3);
            $temp['errors'] = $this->debug;
        return $temp;
    }
    
    public function errorException($errno, $errstr, $errfile, $errline)
    {
        $tmp= [];
        $tmp['line'] = $errline;
        $tmp['file'] = $errfile;
        $tmp['error_number'] = $errno;
        $tmp['error'] = $errstr;
        switch ($errno) {
            case E_USER_ERROR:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_RECOVERABLE_ERROR:
                break;
            case E_USER_WARNING:
            case E_WARNING:
            case E_PARSE:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $this->addDebugError("warning", $tmp);
                break;
            case E_USER_NOTICE:
            case E_NOTICE:
                $this->addDebugError("notice", $tmp);
                break;
            default:
                $this->addDebugError("notice", $tmp);
                break;
        }
        return true;
    }
    
    public function getErrors($type = "")
    {
        switch ($type) {
            default:
                return $this->debug;
            break;
            case "":
                return $this->debug;
            break;
            case "notice":
                return $this->debug['notice'];
            break;
            case "warning":
                return $this->debug['warning'];
            break;
        }
    }
    
    
    public function shutDown()
    {
        //echo "<pre>";
        //print_r(debug_backtrace());
        //echo "</pre>";
    }
    
}