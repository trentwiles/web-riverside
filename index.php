<?php

require __DIR__ . '/vendor/autoload.php';
require 'functions.php';

use RiversideRocks\services as Rocks;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$envRequiredFields = [
    "MYSQL_SERVER", "MYSQL_USERNAME", "MYSQL_PASSWORD", "MYSQL_DATABASE", "YOUTUBE", "ABUSE_IP_DB"
];

foreach ($envRequiredFields as $field) {
    if (!isset($_ENV[$field])) {
        die("\$_ENV doesn't have a required field ${field}");
    }
}

if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];

$ip = $_SERVER['REMOTE_ADDR'];
$router = new \Bramus\Router\Router();
$pug = new Pug();

$ip = $_SERVER['REMOTE_ADDR'];

$servername = $_ENV['MYSQL_SERVER'];
$username = $_ENV["MYSQL_USERNAME"];
$password = $_ENV["MYSQL_PASSWORD"];
$dbname = $_ENV["MYSQL_DATABASE"];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$ipinfo = json_decode(file_get_contents("http://ip-api.com/json/${ip}"), true);
$country = $conn -> real_escape_string(htmlspecialchars($ipinfo["country"]));
$epoch = time();

$sql = "INSERT INTO logs (epoch, country) VALUES ('${epoch}', '${country}')";
$result = $conn->query($sql);

$times = 0;

$sql = "SELECT * FROM logs";
    $result = $conn->query($sql);
    $times = 0;
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $times = $times + 1;
        }
    }
    define("times", $times);

$router->get('/api/visits', function() {
    echo json_encode(times, true);
});




$router->get('/', function() {
    $pug = new Pug();
    $channel_id = "UCoHNPdbSrE2c_g95JgGiBkw";
    $api_key = $_ENV["YOUTUBE"];
    $api_response = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=statistics&id='.$channel_id.'&fields=items/statistics/subscriberCount&key='.$api_key);
    $api_response_decoded = json_decode($api_response, true);
    $subs = $api_response_decoded['items'][0]['statistics']['subscriberCount'];
    $api_response2 = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=statistics&id='.$channel_id.'&fields=items/statistics/viewCount&key='.$api_key);
    $api_response_decoded2 = json_decode($api_response2, true);
    $views = $api_response_decoded2['items'][0]['statistics']['viewCount'];
    $output = $pug->render('views/index.pug', array(
        'visits' => times,
        'subs' => $subs,
        'views' => $views
    ));
    echo $output;
});


$router->get('/about', function() {
    Phug::displayFile('views/about.pug');
});


$router->get('/projects', function() {
    Phug::displayFile('views/projects.pug');
});

$router->get('/watch/(\w+)', function($id) {
    $pug = new Pug();
    $video = json_decode(file_get_contents("https://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=${id}&format=json"), true);
    if($video["author_url"] !== "https://www.youtube.com/channel/UCoHNPdbSrE2c_g95JgGiBkw"){
        header("Location: /videos");
        die();
    }
    $vid = "https://www.youtube.com/embed/" . htmlspecialchars($id);
    $title = $video["title"];
    $output = $pug->render('views/video.pug', array(
        'vid' => $vid,
        'title' => htmlspecialchars($title)
    ));
    echo $output;
});

$router->get('/videos', function() {
    $pug = new Pug();
    $ids = array("1", "2", "3");
    $output = $pug->render('views/watch.pug', array(
        'videos' => $ids
    ));
    echo $output;
});

$router->get('/ip', function() {
    Rocks::abuseDB($ip);
});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    $path = $_SERVER['REQUEST_URI'];
    Rocks::isHacking($path, $_SERVER['REMOTE_ADDR']);
    Phug::displayFile('views/404.pug');
});

$router->run();
