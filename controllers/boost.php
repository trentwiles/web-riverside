<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$router = new \Bramus\Router\Router();

$router->post('/v1/boost', function() {
    header("Content-type: application/json");
    if(isset($_SESSION["username"]))
    {
        die(json_encode(array("message" => "OK"), true));
    }else{
        die(json_encode(array("message" => "Bad Request"), true));
    }
});


$router->run();