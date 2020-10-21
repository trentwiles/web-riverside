<?php

require __DIR__ . '/vendor/autoload.php';

$router = new \Bramus\Router\Router();

$router->get('/', function() {
    echo "It works!";
});

$router->run();