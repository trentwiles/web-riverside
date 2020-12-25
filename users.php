<?php

session_start();
header("X-Powered-By: Riverside Rocks");
header("X-Server: kestral (v2.2)");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protections: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");

require __DIR__ . '/vendor/autoload.php';
require 'functions.php';
require 'security.php';

use RiversideRocks\services as Rocks;
use RiversideRocks\security as Secure;


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$router = new \Bramus\Router\Router();



$router->get('/users/(\w+)', function($id) {
    $pug = new Pug();
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];
    $badge = "";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $discord = $conn -> real_escape_string($id);

    $sql = "SELECT * FROM admins WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $discord);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if(isset($row["username"]))
        {
            $badge = 'fas fa-shield-alt';
        }
    }
    $rtime = 0;
    $sql = "SELECT * FROM read_time WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $discord);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if(!isset($row["username"]))
        {
        }
        else
        {
            $rtime = $rtime + $row["time"];
        }
    }

    $sql = "SELECT * FROM logins WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $discord);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $user = htmlspecialchars($row["username"]);
        if(!isset($row["username"]))
        {
            die(Phug::displayFile('views/user-404.pug'));
        }
        $bio = htmlspecialchars($row["bio"]);
        if(! $bio)
        {
            $bio = "Looks like this user hasn't set a bio!";
        }
        $pre_join = $row["login_time"];
        $join = date("m-d-Y H:i:s", $pre_join);
        if($pre_join == "")
        {
            die(header("Location: /request-error?code=404"));
        }
    }
    if($user == ""){
        die(header("Location: /request-error?code=404"));
    }
    $sql = "SELECT * FROM msg WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $discord);
    $stmt->execute();
    $result = $stmt->get_result();
    $count4 = 0;
    while ($row = $result->fetch_assoc()) {
        $count4 = $count4 + 1;
    }
    $pebbles = Rocks::calcMsg($count4, $pre_join);
    $pebble_url = "https://riverside.rocks/users/" . $user . "/pebbles";
    $output = $pug->render('views/user.pug', array(
        'username' => $user,
        'bio' => $bio,
        'join' => $join,
        'badge' => $badge,
        'pebbles' => $pebbles,
        "pebble_url" => $pebble_url,
        "time" => Rocks::secondsToTime($rtime)
    ));
    echo $output;
});