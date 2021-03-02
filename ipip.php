<?php
require __DIR__ . '/vendor/autoload.php';

use IPTools\Network;

$hosts = Network::parse($_GET["ip"])->hosts;
$sendl = array();
foreach($hosts as $ipas) {
    array_push($sendl, (string)$ipas);
}
$echo = json_encode($sendl, true);
echo $echo;