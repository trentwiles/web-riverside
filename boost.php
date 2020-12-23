<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$router = new \Bramus\Router\Router();

$router->get('/test', function() {
    die("lol");
});


$router->run();