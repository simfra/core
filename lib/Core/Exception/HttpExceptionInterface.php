<?php
namespace Core\Exception;

interface HttpExceptionInterface
{
    public function getStatusCode();
}