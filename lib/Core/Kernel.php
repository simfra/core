<?php
namespace Core;

use App\User\User;
use Core\Exception\NotFoundException;
use Core\Http\Request\Request;
use Core\Route\Route;
use Core\Exception\FatalException;
use Core\Exception\PermissionException;
use Core\Config\Config;
use Core\Http\Response\Response;
use Core\Objects\AppArray;
use Core\Objects\AppObject;

abstract class Kernel
{
    public $start_time = null;
    public $application_name = null;
    public $isProd = null;
    public $config = [];
    public $page = null;
    private $booted = false;
    private $container = null;


    /**
     * Bootstrap constructor.
     * @param string $application_name
     * @param string $application
     * @throws FatalException
     */
    public function __construct($application_name = "", $application = '')
    {
        $this->start_time = microtime(true);
        $this->container = new Container();
        set_exception_handler(array($this, "handleException"));
        $this->application_name = $application_name;
        if ("prod" === $application) {
            $this->isProd = true;
        } elseif ("dev" === $application) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            $this->isProd = false;
        } else {
            throw new FatalException("App", "No type of application specified.
             You need to set application = 'prod' - for production application or 'dev' to development application");
        }
        return $this;
    }

    /**
     * @return Container|null
     * @throws FatalException
     */
    public function getContainer()
    {
        if (null === $this->container) {
            throw new FatalException("App", "Unable to load default container");
        }
        return $this->container;
    }

    public function getPublicPath()
    {
        return realpath(__DIR__);
    }


    public function getApplicationPath()
    {
        return realpath(__DIR__ . "/../../App/" . $this->application_name) . "/";
    }

    public function getApplicationNamespace()
    {
        return $this->application_name . "\\";
    }

    public function addBundle($bundle, $name = "")
    {
        return $this->getContainer()->addBundle($bundle, $name);
    }


    public function bootUp()
    {
        $this->config = ($this->getContainer()->addBundle(new Config))->getConfig($this->getApplicationPath(), $this->application_name);
        if (!method_exists($this, "registerBundles")) {
            throw new FatalException("Kernel", "Unable to find registerBundle method!");
        }
        $this->registerBundles();
        if ($this->isProd === true) {
            set_error_handler(function () {
                return true;
            }); // do not show any notice or warning in production enviroment
        }
        foreach ($this->getContainer()->listBundles() as $name => $bundle) {
            $bundle->setContainer($this->getContainer());
            $bundle->defaultConfig($this->getContainer()->getBundleConfig($name, $this->config));
            $bundle->bootUp($this);
        }
        // Register all widgets
        $this->registerWidget("widget");
        $this->registerWidget("link");
    }

    private function handlePage($page, $request)
    {
/*        //if ($pag)
        echo "<pre>";
        print_r($page);
        echo "</pre>". var_dump($page->struct->get("https"));*/
        if($page->struct->isset("https") && ($page->struct->get("https") != null and ($page->struct->get("https") == true || $page->struct->get("https") == 1)) && $request->isHttps == false) {
            header("Location: " . "https://" . $_SERVER['SERVER_NAME']. $_SERVER['REQUEST_URI']);
            die();
        }
        $this->checkPagePermissions();
        $controller_name = $this->getApplicationNamespace() . "Controller\\" . $page->struct->get("controller");
        $controller = new $controller_name($this);
        return $controller->callControllerMethod($page->struct->get("method"));
    }

    public function redirectByName($name, $lang, $params = [])
    {
        $struct = Route::getConfig($this->getApplicationPath());
        $temp_params = "";
        if (count($params) > 0) {
            foreach($params as $key => $value) {
                $temp_params[] =  "$key=$value";
            }
            //print_r($temp_params);
            $param = "?" . implode("&", $temp_params);
            //echo $a;
        }
        foreach($struct as $key=>$value) {
            if(mb_strtolower($value['name']) == mb_strtolower($name)) {
                $url = "https://" . $_SERVER['SERVER_NAME']. rtrim($value['url'][$lang],"/");
                //echo "**$url**";
                //echo "URL Redirect $url$param";
                header("Location: $url". $param);
                die();
            }
        }
        //throw new NotFoundException("Redirect page not found",404);
        return false;
    }

    public function redirectById($id)
    {
        $struct = Route::getConfig($this->getApplicationPath());
        foreach($struct as $key=>$value) {
            if($value['id'] == $id) {
                $url = "https://" . $_SERVER['SERVER_NAME']. rtrim($value['url'],"/"). "/";
                header("Location: $url");
                die();
            }
        }
    }

    public function redirectToUrl($url)
    {
        //echo "Location: " . "https://" . $_SERVER['SERVER_NAME']. $url;//

        header("Location: " . "https://" . $_SERVER['SERVER_NAME']. $url);
        die();
    }

    public function checkPagePermissions($page_name = "")
    {
        //$user_permissions = $this->
        if (trim($page_name) == "") {
            $struct = $this->page->struct;
        } else {
            $struct_tmp = Route::getConfig($this->getApplicationPath());
            $struct = [];
            foreach ($struct_tmp as $key => $value) {
                if ($value['name'] == $page_name) {
                    $struct = $struct_tmp[$key];
                    break;
                }
            }
            if (count($struct) == 0){
                trigger_error("Page with given name ($page_name) doesn't exists");
                return false;
            }
        }
        if ($struct->authorised == false) {
          //  echo "bez logowania";
            return true;
        }
        //echo "musi byc zalogowany";
        //die("User musi byc zalogowany");
        //echo "AAAA<pre>";
        //print_r($struct->permissions);
        //echo "</pre>";
        $session = $this->getContainer()->getBundle("Session");
        $session->checkSession();
        //var_dump(Route::checkPermissions($this->page->struct, $session->getPermissions(), 1));
        if (Route::checkPermissions(explode(",",$struct->permissions), $session->getPermissions()) == false) {
            throw new PermissionException("Forbidden", "You don't have permission to access this page");
        }
        //
        //echo "<pre>KERNEL";
        //print_r($session->getData());
        //echo "</pre>";
        return false;
    }

    public function getService($name, $singleton = false)
    {
        return $this->getContainer()->getService($name, $singleton);
    }
    
    public function handleRequest(Request $request)
    {
        try {
            if ($this->booted === false) {
                $this->bootUp();
            }
//            $route = new Route($this);
            $this->config = new AppObject($this->config);
            $this->page = new AppObject((new Route($this))->checkUrl($request, $request->getPreferredLanguage($this->config->app->languages)));
            $this->page->add("request", $request);
            $this->page->add("preferred_lang", $request->getPreferredLanguage($this->config->app->languages));
            $response = $this->handlePage($this->page, $request);
        } catch (\Error $error) {
            $response = $this->HandleException($error);
        } catch (\ErrorException $error) {
            $response = $this->HandleException($error);
        } catch (\Exception $exception) {
            $response = $this->HandleException($exception);
        }
        //die("@@@@@".$this->page->struct);
        //echo "***<pre>".print_r($this->page->struct->getAll()) ."</pre>&&&";
        //die($this->page->struct->get("type"));
        //echo $response->content;
        if (!$this->isProd && $this->getContainer()->isBundle("Debug") && $this->page->struct->get("type") == "content") {
            $response->content = $this->getContainer()->getBundle("Debug")->makeDevToolbar($response->getContent());
        }
        return $response;
    }
    
    // @TODO: Refactor this
    public function handleException($exception)
    {
        $exception->isProd = $this->isProd; // to determine if exception been thrown in production/dev enviroment
        $temporary= new AppObject([
                "controller" => method_exists($exception, "getName") ? $exception->getName(): "Unknown name",
                "method" => __FUNCTION__,
                "preferred_lang" => (method_exists($this->page, "get") ) ? $this->page->preferred_lang : "en",
                "lang" => isset($this->page->lang) ? $this->page->lang : "en"
            ]);
        $this->page = new AppObject(["struct" => $temporary,
        "preferred_lang" => (method_exists($this->page, "get") ) ? $this->page->preferred_lang : "en",
                "lang" => isset($this->page->lang) ? $this->page->lang : "en"
        ]);
        if ($this->container === null ||  $this->container->isBundle("View") === false) { // No templates system
            http_response_code((method_exists($exception, "getStatusCode") ? $exception->getStatusCode() :500));
            $content = "Fatal Error occured with message <b>". $exception->getMessage() . "</b> in file " . $exception->getFile(). " line " . $exception->getLine() ;
            $response = new Response($content, (method_exists($exception, "getStatusCode") ? $exception->getStatusCode() :500), ((method_exists($exception, "getHeaders")) ? $exception->getHeaders() : ""));
            $response->sendResponse();
            return $response;
        }
        $template = $this->getContainer()->getBundle("View");
        $template->default_assigned = true;
        if ($this->isProd) { // When Application is production - show error page
            (method_exists($exception, "getMessage") ? $message = $exception->getMessage() : $message = "Unknown message");
            $template->assign("message", $message);//$exception->getMessage());
            (method_exists($exception, "getTitle") ? $title = $exception->getTitle() : $title = "Error");
            $template->assign("title", $title);
            (method_exists($exception, "getTemplate")) ? $temp_file =  $exception->getTemplate() : $temp_file = "Error/Error.tpl";
            if (file_exists(APP_DIR . "templates/".$temp_file)) {
                $content = $template->fetch((method_exists($exception, "getTemplate")) ? $exception->getTemplate() : "Error/Error.tpl");//"Error/Error500.tpl");
            } else { // No template file for error
                $content = "<h1>{$title}</h1><h3>{$message}</h3>";
            }
            $response = new Response($content, (method_exists($exception, "getStatusCode") ? $exception->getStatusCode() :500), (method_exists($exception)) ? $exception->getHeaders() : "");
            $response->sendResponse();
            return $response;
        } else { // Development enviroment - show Exception page with debug info
            if (is_a($exception, "\Error")) {
                $template->assign("title", "Error Exception");
                $template->assign("name", "Error exception");
                $debug = [
                        "class"  => method_exists($exception, "getFile") ? $exception->getFile() : "Unknown class",
                        "line"  => method_exists($exception, "getLine") ? $exception->getLine() : "Unknown line",
                        "trace" => method_exists($exception, "getTrace") ? $exception->getTrace() : "Unknown trace"
                    ];
                $template->assign("debug_info", $debug);
            } else {
                $template->assign("title", method_exists($exception, "getTitle")
                    ? $exception->getTitle(): "Unknown title");
                $template->assign("name", method_exists($exception, "getName")
                    ? $exception->getName(): "Unknown name");
                $template->assign("debug_info", method_exists($exception, "getDebug")
                    ? $exception->getDebug() : "Unknown debug info");
            }
            $template->assign("message", method_exists($exception, "getMessage")
                ? $exception->getMessage(): "Unknown message");
            $template->assign("content", ob_get_contents());
            $content = $template->fetch("Exception/fatal.tpl");
            //$response = (new Response($content, $exception->getStatusCode()))->addHeader("aaaa");
            $response = new Response($content, (method_exists($exception, "getStatusCode")
                ? $exception->getStatusCode() : 500), (method_exists($exception, "getHeaders")
                ? $exception->getHeaders() : []));
          //  return $response;
        }
        if (!$this->isProd && $this->getContainer()->isBundle("Debug") && $this->page->struct->get("type") == "content") {
            $response->content = $this->getContainer()->getBundle("Debug")->makeDevToolbar($response->getContent());
        }
        $response->sendResponse();
    }
    
    
    public function __destruct()
    {
        if (ob_get_length() && !$this->isProd && $this->page->struct->get("type") == "content") {
            echo "*** WARNING ***<br /> Unsend content in buffer! ";
        } else {
            //ob_end_clean();
        }
    }

    public function replaceUrlParams($url, $params = [])
    {
        //trigger_error("Url :" .$url);
        $ret  = [];
        foreach( explode("/", $url) as $key => $v1) {
            if ($v1 != "") {
                $t1 = Route::getStringBetween($v1, "[", "]");
                //trigger_error(var_dump($t1,true));
                if ($t1 === "") {
                    // part of url doesn't contain any modifier'
                    $ret[] = $v1;
                } else {
                    $modifiers = Route::splitModifiers($t1);
                    //$temp = print_r($modifiers, true);
                    if (isset($modifiers['name']) ) {
                        $temp = $modifiers['name'];
                        if (isset($params[$temp])) {
                            $ret[] = $params[$temp];
                        } elseif (isset($modifiers['default'])) {
                            $ret[] = $modifiers['default'];
                        }
                    } else {
                        $ret[]= $v1;
                    }
                }

            }
        }
        return "/".implode("/", $ret);
    }
    
    public function close($request, $response)
    {
        ob_end_clean();
        if ($response instanceof Response && !$response->isSend) {
            $response->sendResponse();
        }
        exit();
    }
    


    /**
     * Bootstrap::__call()
     * Funkcja wykonywana gdy klasa nie zawiera wywolywanej przez uzytkownika metody
     * @param mixed $name
     * @param mixed $arg
     * @return string
     */
    public function __call($name, $arg)
    {
        return "Call unknown method $name ";
    }



    public function registerWidget($name)
    {
        //return $this->getBundle("View")->registerPlugin($type, $name, $callback);
        switch($name) {
            case "widget":
                return $this->getContainer()->getBundle("View")->registerPlugin("function", $name, [$this, "handleWidget"]);
                break;
            case "link":
                return $this->getContainer()->getBundle("View")->registerPlugin("function", $name, [$this, "handleLink"]);
                break;
        }

    }


    public function handleLink($params, $smarty)
    {
        if (isset($params['name']) && $params['name']!= "") {
            $name = $params['name'];
        }
        if (isset($params['lang']) && $params['lang']!= "") {
            $lang = $params['name'];
        } else {
            $lang = $this->page->lang;
        }
        $link = "";
        $struct = Route::getConfig($this->getApplicationPath());
        foreach($struct as $key=>$value) {
            if($value['name'] == $name) {
                return $this->replaceUrlParams($value['url'][$lang], $params);
              }
        }
        trigger_error("No link with specific name ($name)");
        return "/#";
    }

    public function handleWidget($params, $smarty)
    {
        try {
            if (isset($params['controller']) && isset($params['method'])) {
                // TODO: make a widget permission system
                $controller_name = $this->getApplicationNamespace() . "Controller\\" . $params['controller'];
                if (class_exists($controller_name)) {
                    $controller = new $controller_name($this);
                    if (method_exists($controller, $params['method'])) {
                        return $controller->{$params['method']}($params);
                    } else {
                        trigger_error("Method " . $params['method'] . " doesn't exists in controller: " . $controller_name, E_USER_WARNING);
                        return;
                    }
                } else {
                    trigger_error("Controller " . $params['controller'] . " doesn't exists", E_USER_WARNING);
                    return;
                }
            }
        }catch (\Error $error) {
            trigger_error("Widget function error (file: {$error->getFile()}  line: {$error->getLine()}) with message  " . $error->getMessage(), E_USER_WARNING);
            $error = null;
            return;
        } catch (\ErrorException $error) {
            //die();
            $response = $this->HandleException($error);
        } catch (\Exception $exception) {
            //die();
            $response = $this->HandleException($exception);
        }
        return $response;
    }


    public static function loadApp($app_name, $app_type)
    {
        $kernel = "\\".$app_name."\\AppKernel";
        if (class_exists($kernel)) {
            return new $kernel($app_name, $app_type);
        } else {
            http_response_code(500);
            $content = "Fatal Error occured with message <b>Unable to load app: $app_name</b>";
            $response = new Response($content, 500, false);
            $response->sendResponse();
        }
    }

    public function getIP()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (empty($ip) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (empty($ip) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $ip;
    }




}
