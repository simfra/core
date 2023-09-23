<?php
namespace Core\Interfaces;

interface ArrayMethod
{
    public function get($variable);
    public function set($variable, $value);
}