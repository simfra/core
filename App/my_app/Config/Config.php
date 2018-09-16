<?php
return [
    "bundles" => [
            "Database" => [
                "host" => "blindsbypost.co.uk",
                "port" => "3306",
                "username" => "phpmyadmin",
                "password" => "PolSyn123",
                "dbname" => "hudson_dev",
                "type" => "Mysql"
            ],
            "Debug" => [
                "show_toolbar" => true,
                "minimalized_toolbar" => false
,
                "theme" => "default"

            ],
            "View" => [
                "templateDir" => APP_DIR . "templates/",
                "compileDir" =>  APP_DIR . "cache/templates/",
            ],
    ],
    "app" => [
        "languages" => ["en"]
    ]
];