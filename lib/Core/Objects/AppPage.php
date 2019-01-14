<?php
namespace Core\Objects;

class AppPage
{
    public $struct;
    public $request;


    public function __construct()
    {
        $this->struct = [
            "type" => "content",
            "active" => true,
            "controller" => "",
            "method" => "",
            "parent" => 0,
            "visible" => true,
            "show_in_menu" => true,


        ];
    }

}

/*
array (
'name' => 'start',
'typ' => 'content',
'active' => '1',
'controller' => 'Front',
'method' => 'index',
'parent' => '0',
'visible' => '1',
'show_in_menu' => '1',
'template' => '1',
'content' => '',
'target' => '-1',
'load_lang' => '0',
'hierarchy' => '1',
'url' =>
array (
'en' => '/',
),
'menu' =>
array (
'en' => 'Start',
),
'title' =>
array (
'en' => 'Home page',
),
'id' => 1,
)