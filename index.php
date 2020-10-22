<?php

require __DIR__ . '/vendor/autoload.php';

$router = new \Bramus\Router\Router();
$pug = new Pug();


$router->get('/', function() {
    Phug::displayFile('views/index.pug');
});

$router->get('/about', function() {
    Phug::displayFile('views/about.pug');
});

$router->get('/about', function() {
    header("Location: /");
});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    Phug::displayFile('views/404.pug');
});

$router->run();
