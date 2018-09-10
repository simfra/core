<?php
namespace Core\Http\Response;

use Core\Exception\HttpException;

class Response
{
    const HTTP_OK= 200;
    const HTTP_NOT_FOUND = 404;
    private $headers = array();
    private $kernel = null;
    private $useDebug = true;
    public $isSend = false;
    public $content = "";
    public $purify = true;

    
    public function __construct($content = "", $response_code = "", $headers = [])
    {
        if (!class_exists("tidy")) {
            trigger_error("Class Tidy not found. Source will not be purified!");
        }
        if ($headers !== []) {
            $this->addHeaders($headers);
        } else {
            $this->addHeader("Content-Type", "text/html");
        }
        if ($response_code != "") {
            http_response_code($response_code);
        }
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }
    
    private function sendHeaders($headers)
    {
        if (!headers_sent()) {
            foreach ($headers as $key => $header) {
                header($key . ": " .$header);
            }
        }
    }
    
    private function sendContent($content)
    {
        if (ob_get_level()) {
            ob_end_clean();
        }
        echo $content;

        /*
        $doctype = (new \DOMImplementation)->createDocumentType("html");
        $dom = (new \DOMImplementation)->createDocument(null, null, $doctype);
        $dom->loadHTML($content, LIBXML_DTDLOAD);
        $a = $dom->createElement("aaa", "afsdfsd");
        $dom->appendChild($a);
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = true;
        $dom->normalizeDocument();
        echo $dom->saveHTML();

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Doctype', 'XHTML 1.0 Strict');
        $config->set('HTML.Allowed', 'link', 'script', 'div','span', 'head','body','meta');
        $purifier = new \HTMLPurifier($config);
        $clean_html = $purifier->purify($content);
        echo $content;// $clean_html;
        */
    }

    public function clean($content)
    {
        if (class_exists("tidy")) {
            $config = array(
                'indent'         => true,
                'output-xhtml'   => true,
                'wrap'           => 200,
                'drop-empty-paras' => false,
                'drop-empty-elements' => false,
                'new-empty-tags' => 'command embed keygen source track wbr span div i',

            );
            $tidy = new \tidy;
            $tidy->parseString($content, $config, 'utf8');
            $tidy->repairString($content);
            return $tidy;
        }
        return $content;
    }
    
    public function sendResponse()
    {
        //global $app;
        //die();
        //$this->content .="IP:". print_r($app, true);
        // Devtool if Debug mode is enable
        //echo "aaaa";
//        if ($this->useDebug === true && $this->kernel != null && $this->kernel->getContainer()->isBundle("Debug") && !$this->kernel->isProd) {
//            try {
//                $this->content = $this->kernel->getContainer()->getBundle("Debug")->makeDevToolbar($this->content);
//            } catch (\Exception $e) {
//                die("Exception from Debug ".$e->getMessage());
//            }
//        }

        if ($this->purify == true) {
            echo "1";

            $this->content = $this->clean($this->content);
        }
        ob_clean();
        //die("Asasda");
        $this->addHeader('Content-Length', strlen($this->content));
        $this->sendHeaders($this->headers);
        $this->sendContent($this->content);
        $this->isSend = true;
        exit();
    }
    
    public function addHeader($key, $value = "")
    {
        if (trim($key) != "") {
            $this->headers[$key] = $value;
        }
        return $this;
    }

    public function addHeaders($headers)
    {
        foreach ($headers as $key => $value) {
            $this->headers[$key] = $value;
        }
        return $this;
    }
}