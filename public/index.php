<?php

use App\Controllers\HomeController;
use Core\Routing\Router;

error_reporting(E_ALL);
ini_set('display_errors', 'on');

require dirname(__DIR__) . '/vendor/autoload.php';

$router = new Router();

$router->get('/', [HomeController::class, 'index']);

$router->fallback(function () {
    echo 'Not found';
});

$router->run();
