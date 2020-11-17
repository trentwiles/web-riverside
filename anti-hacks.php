<?php
require __DIR__ . '/vendor/autoload.php';

require 'functions.php';
require 'security.php';

use RiversideRocks\services as Rocks;
use RiversideRocks\security as Secure;

$router = new \Bramus\Router\Router();
$pug = new Pug();



