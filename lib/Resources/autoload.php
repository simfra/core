<?php

/*
 * /App/app_name
 * /App/lib/
 * /lib/
 */
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require __DIR__ . '/../../vendor/autoload.php'; // From composer
} else {
    die("Please install composer package or install SIMFRA Framework with vendors");
}

require_once __DIR__ .'/../Core/Enums/App_Type.php';
require_once __DIR__ .'/../Core/Kernel.php';
/*
spl_autoload_register(function ($class) {
    echo "Namespace: " .__NAMESPACE__." Class: $class <br/>";
    $class_file = str_replace('\\', '/', $class) .".php";
    $dir = realpath(__DIR__ . "/../../") . "/";
    if (file_exists($dir . "App/" . $class_file)) {
        include_once($dir . "App/" . $class_file);
    } elseif (file_exists($dir . "lib/" . $class_file)) {
        include_once($dir . "lib/" . $class_file);
    } elseif (file_exists($dir . $class_file)) {
        include_once($dir . $class_file);
    } elseif (defined('APP_DIR') && file_exists(APP_DIR . $class_file)) {
        include_once(APP_DIR . $class_file);
    } elseif (defined('APP_DIR') && file_exists(APP_DIR . "lib/" . $class_file)) {
        include_once(APP_DIR . "lib/" . $class_file);
    }
});
*/
