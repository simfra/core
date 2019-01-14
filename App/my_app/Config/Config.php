<?php
return [
    "bundles" => [
            "Database" => [
                "host" => "",
                "port" => "",
                "username" => "",
                "password" => "",
                "dbname" => "",
                "type" => "Mysql"
            ],
            "Debug" => [
                "show_toolbar" => true,
                "minimalized_toolbar" => false,
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