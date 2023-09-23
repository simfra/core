<?php
namespace Core\Exception;

class NotFoundException extends HttpException
{
    public function getTitle()
    {
        return "404 - Not found";
    }

    public function getStatusCode()
    {
        if ($this->isProd) {
            return 404;
        } else {
            return 500;
        }
    }

    public function getTemplate($isProd = true)
    {
        return "Error/404.tpl";
    }


    public function getHeaders()
    {
        return ["X-Cache"=> "None"];
    }


}