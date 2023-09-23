<?php
ob_start();
//use \Core\Exception\FatalException;
//use Core\Request\Request;
use Core\Enums\App_Type;

require __DIR__ . '/../lib/Resources/autoload.php';
$app = \Core\Kernel::loadApp("my_app", App_Type::DEV);
$request = Core\Request\Request::Create();
$response = $app->handleRequest($request);
if ($response instanceof \Core\Response\Response) {
    $response->sendResponse();
} else {
    throw new FatalException("Init", "Incorrect response from Kernel (not a \Core\Response\Response object).");
}
$app->close($request, $response);
