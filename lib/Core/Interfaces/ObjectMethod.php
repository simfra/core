<?php
namespace Core\Interfaces;

interface ObjectMethod
{
    public function get($variable);
    public function set($variable, $value);
}