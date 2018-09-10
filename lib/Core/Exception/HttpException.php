<?php
namespace Core\Exception;

class HttpException extends \RuntimeException implements HttpExceptionInterface
{
    private $statusCode;
    public $isProd;
    public $headers = [];
    public function __construct($statusCode, $Message, $previous = null, $code = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($Message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getName()
    {
        return get_class($this);
    }

    public function getTitle()
    {
        return "Http Exception";
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    // @TODO: parse debug info including loading part of source file containing broken code
    public function getDebug()
    {
        $debug = debug_backtrace();
        return $debug;
    }

    public function getTemplate()
    {
        if ($this->isProd) { // By default on production site use 500 template
            return "Error/500.tpl";
        } else { // But on dev site use fatal error template
            return "Exception/fatal.tpl";
        }
    }
}