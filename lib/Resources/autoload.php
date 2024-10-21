<?php

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require __DIR__ . '/../../vendor/autoload.php'; // From composer
} else {
    die("Please install composer package or install SIMFRA Framework with vendors");
}

require_once __DIR__ .'/../Core/Enums/App_Type.php';
require_once __DIR__ .'/../Core/Kernel.php';

