<?php
namespace Core\Response;

class Response
{
    private $content = "";
    public function __construct($content = "", $response_code = "", $headers = [])
    {
        //return new static();
        $a = (new \Core\Response\Http\Response\Response($content, $response_code, $headers));
        //print_r($a);
        $this->content = $content;

        return $a;
    }

//    public function SendResponse()
//    {
//        return print_r($this);
//    }

    public function getResponse()
    {
        //return new \Core\Response\Http\Response\Response($a, $b, $c);
        return new static();
    }

    public function getContent()
    {
        return $this->content;
    }

}