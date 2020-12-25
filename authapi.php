<?php

session_start();
header("X-Powered-By: Riverside Rocks");
header("X-Server: kestral (v2.2)");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protections: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$router = new \Bramus\Router\Router();



$router->post('/v1/isauth', function() {
    header("Content-type: application/json");
    if(isset($_SESSION["username"]))
    {
        die(json_encode(array("message" => "OK"), true));
    }else{
        die(json_encode(array("message" => "Bad Request"), true));
    }
});


$router->run();