<?php

require __DIR__ . '/vendor/autoload.php';

$router = new \Bramus\Router\Router();

$router->get('/', function() {
    Phug::displayFile('views/index.pug');
});

$router->get('/about/', function() {
    Phug::displayFile('views/about.pug');
});

$router->run();
