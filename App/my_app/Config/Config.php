<?php
return [
    "bundles" => [
            "Database" => [
                "host" => "localhost",
                "port" => "5432"
            ],
            "Debug" => [
                "show_toolbar" => true,
                "minimalized_toolbar" => false,
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