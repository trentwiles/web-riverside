<?php

require __DIR__ . '/vendor/autoload.php';
require 'functions.php';
require 'security.php';

use RiversideRocks\services as Rocks;
use RiversideRocks\security as Secure;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$envRequiredFields = [
    "MYSQL_SERVER", "MYSQL_USERNAME", "MYSQL_PASSWORD", "MYSQL_DATABASE", "YOUTUBE", "ABUSE_IP_DB", "UPLOAD"
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

    $sql = "SELECT country, count(*) as SameValue from logs GROUP BY country ORDER BY SameValue DESC";
    $result = $conn->query($sql);
    $countries = array();
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $con = htmlspecialchars($row["country"]);
            $val = htmlspecialchars($row["SameValue"]);
            array_push($countries, $con, $val);
        }
    }

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

$router->get('/about/legal', function() {
    Phug::displayFile('views/legal.pug');
});

$stat = Rocks::statcord("764485265775263784", "logan");

$router->get('/about/stats', function() {
    $pug = new Pug();
    $stat = Rocks::statcord("764485265775263784", "logan");
    $output = $pug->renderFile('views/count.pug', array(
        'bot_users' => $stat[0],
        'bot_servers' => $stat[1],
        'bot_commands' => $stat[2],
        'requests' => times
    ));
    echo $output;
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

$router->get('/index.php', function() {
    header("Location: https://www.youtube.com/watch?v=E2Q52cVx7Bo");
    die();
});

$router->get('/ip', function() {
    Rocks::abuseDB($ip);
});

/*================================

     UPLOAD CONTROLLER CODE

===============================*/

$router->get('/admin/upload', function() {
    Phug::displayFile('views/upload.pug');
});

$router->post('/admin/upload', function() {
    $pug = new Pug();
    if($_POST["key"] !== $_ENV["UPLOAD"]){
        $output = $pug->renderFile('views/upload-fail.pug', array(
            'errors' => '400: Bad Request. You are missing a valid upload key.'
        ));
        die($output);
    }
    if($_POST["one"] == "public"){
        $storage = new \Upload\Storage\FileSystem('a');
        $dir = 'a';
    }else{
        $storage = new \Upload\Storage\FileSystem('assets/serve/production/app');
        $dir = 'assets/serve/production/app';
    }
    $file = new \Upload\File('foo', $storage);

    $new_filename = uniqid();
    $file->setName($new_filename);

$data = array(
    'name'       => $file->getNameWithExtension(),
    'extension'  => $file->getExtension(),
    'mime'       => $file->getMimetype(),
    'size'       => $file->getSize(),
    'md5'        => $file->getMd5(),
    'dimensions' => $file->getDimensions()
);

// Try to upload file
try {
    // Success!
    $file->upload();
    $path = "https://riverside.rocks/${dir}/" . $data["name"];
    

    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $epoch = time();
    $sql = "INSERT INTO uploads (`url`, epoch) VALUES ('${path}', '${epoch}')";
    $result = $conn->query($sql);
    $output = $pug->renderFile('views/uploaded.pug', array(
        'url' => $path
    ));
    echo $output;
} catch (\Exception $e) {
    // Fail!
    $errors = $file->getErrors();
    $output = $pug->renderFile('views/upload-fail.pug', array(
        'errors' => $errors
    ));
    echo $output;
}
});


$router->get('/community', function() {
    Phug::displayFile('views/community-temp.pug');
});

$router->get('/admin', function() {
    header("Location: /admin/upload/");
    die();
});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    
    $hacks = Secure::returnExploits();
    
    $url = $_SERVER["REQUEST_URI"];
    
    $user_agent_blacklist = array(
        "Go-http-client/1.1",
        "Mozilla/5.0 zgrab/0.x",
        "python-requests/2.24.0"
    );
    
    $ua = $_SERVER['HTTP_USER_AGENT'];
    
    if(isset($hacks[$url]) || isset($baduseragent[$ua])){
        $mes = "AUTOMATED REPORT: " . $hacks[$url];
    }
    
    if(isset($baduseragent[$ua])){
        $mes = "AUTOMATED REPORT: Port Scanning: " . $url;
    }
        
    if(isset($mes)){
        $ip = $_SERVER['REMOTE_ADDR'];
        $client = new GuzzleHttp\Client([
            'base_uri' => 'https://api.abuseipdb.com/api/v2/'
          ]);
          
          $response = $client->request('POST', 'report', [
              'query' => [
                  'ip' => "${ip}",
                  'categories' => '15',
                  'comment' => "${mes}"
              ],
              'headers' => [
                  'Accept' => 'application/json',
                  'Key' => $_ENV["ABUSE_IP_DB"]
            ],
          ]);
          
          $output = $response->getBody();
          // Store response as a PHP object.
          $ipDetails = json_decode($output, true);
        }
    Phug::displayFile('views/404.pug');
});

$router->run();
