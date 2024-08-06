<?php

error_reporting(E_ALL);
require __DIR__ . '/../lib/Resources/autoload.php';
$app = Core\Kernel::loadApp("my_app", "dev");
$response = $app->handleRequest(Core\Request\Http\Request::Create());
if ($response instanceof Response) {
    $response->sendResponse();
} else {
    throw new FatalException("Init", "Incorrect response from Kernel (not a \Core\Response\Response object).");
}

