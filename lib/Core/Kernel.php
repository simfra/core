<?php
namespace Core;

use Core\Http\Request\Request;
use Core\Route\Route;
use Core\Exception\FatalException;
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
    }

    private function handlePage($page)
    {
        $controller_name = $this->getApplicationNamespace() . "Controller\\" . $page->struct->get("controller");
        $controller = new $controller_name($this);
        return $controller->callControllerMethod($page->struct->get("method"));
    }

    
    
    public function handleRequest(Request $request)
    {
        try {
            if ($this->booted === false) {
                $this->bootUp();
            }
            $this->page = new AppObject((new Route($this))->checkUrl($request));
            $this->page->add("request", $request);
            //echo "<pre>@@@@";
            //print_r($this->page->struct);
            //echo "</pre>";
            $this->config = new AppObject($this->config);
            $this->page->add("preferred_lang", $request->getPreferredLanguage($this->config->app->languages));
            // checking url
            echo "<pre>";
            //print_r($this->page);
            echo "</pre>";
            //$this->lang = $request->languages;
            $response = $this->handlePage($this->page, $request);
        } catch (\Error $error) {
            $response = $this->HandleException($error);
        } catch (\ErrorException $error) {
            $response = $this->HandleException($error);
        } catch (\Exception $exception) {
            $response = $this->HandleException($exception);
        }
        if (!$this->isProd && $this->getContainer()->isBundle("Debug")) {
            $response->content = $this->getContainer()->getBundle("Debug")->makeDevToolbar($response->getContent());
        }
        return $response;
    }
    
    // @TODO: Refactor this
    public function handleException($exception)
    {
        echo "<pre>";
        //print_r($exception);
        echo "</pre>";
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
            $content = "Fatal Error occured with message <b>". $exception->getMessage() . "</b>";
            $response = new Response($content, (method_exists($exception, "getStatusCode") ? $exception->getStatusCode() :500), ((method_exists($exception, "getHeaders")) ? $exception->getHeaders() : ""));
            $response->sendResponse();
            return $response;
        }
        $template = $this->getContainer()->getBundle("View");
        if ($this->isProd) { // When Application is production - show error page
            $template->assign("message", method_exists($exception, "getMessage") ? $exception->getMessage() : "Unknown message");//$exception->getMessage());
            $template->assign("content", ob_get_contents());
            $content = $template->fetch($exception->getTemplate());//"Error/Error500.tpl");
            $response = new Response($content, (method_exists($exception, "getStatusCode") ? $exception->getStatusCode() :500), $exception->getHeaders());
        } else { // Development enviroment - show Exception page with debug info
            echo "dsfsdfsdfsdfsdf";
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
        if (!$this->isProd && $this->getContainer()->isBundle("Debug")) {
            $response->content = $this->getContainer()->getBundle("Debug")->makeDevToolbar($response->getContent());
        }
        $response->sendResponse();
        //return $response;// new \Core\Http\Response\Response($this, $content);
    }
    
    
    public function __destruct()
    {
        if (ob_get_length() && !$this->isProd) {
            echo "*** WARNING ***<br /> Unsend content in buffer! ";// . print_r(debug_backtrace());
        }
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




}
