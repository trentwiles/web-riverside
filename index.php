<?php

require __DIR__ . '/vendor/autoload.php';
require 'functions.php';

use RiversideRocks\functions as Rocks;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];

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
    echo times;
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

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    $hacks = array(
        "/.env" => "Tried to access .env file",
        "/api/jsonws/invoke" => "Tried to POST web API, /api/jsonws/invoke",
        "/.git//index" => "Attempted to access git files, /.git//index",
        "/?a=fetch&content=<php>die(@md5(HelloThinkCMF))</php>" => "ThinkPHP exploit. /?a=fetch&content=<php>die(@md5(HelloThinkCMF))</php>",
        "/?XDEBUG_SESSION_START=phpstorm" => "PHPSTORM Debug hack",
        "/solr/admin/info/system?wt=json" => "Trying to access solr admin page.",
        "/boaform/admin/formLogin" => "Trying to access admin login: /boaform/admin/formLogin",
        "/config/getuser?index=0" => "Trying to access configuration files: /config/getuser?index=0",
        "/test/.env" => "Attempting to access .env file",
        "/laravel/.env" => "Attempting to access .env file",
        "/admin/.env" => "Attempting to access .env file",
        "/system/.env" => "Attempting to access .env file",
        "/vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php" => "Attempting to access vendor files: /vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php",
        "/por/login_psw.csp" => "Trying to access admin login pages: /por/login_psw.csp",
        "/ui/login.php" => "Trying to access admin login pages: /ui/login.php",
        "/cgi-bin/login.cgi?requestname=2&cmd=0" => "Trying to access admin login pages: /cgi-bin/login.cgi?requestname=2&cmd=0",
        "/GponForm/diag_Form?images/" => "Odd Request, trying to access some sort of form: /GponForm/diag_Form?images/",
        "//vendor/phpunit/phpunit/phpunit.xsd" => "Trying to access PHPUnit scripts: //vendor/phpunit/phpunit/phpunit.xsd",
        "//web/wp-includes/wlwmanifest.xml" => "Attempting to access Wordpress wlwmanifest.xml file.",
        "//wordpress/wp-includes/wlwmanifest.xml" => "Attempting to access Wordpress wlwmanifest.xml file.",
        "//wp-includes/wlwmanifest.xml" => "Attempting to access Wordpress wlwmanifest.xml file.",
        "//shop/wp-includes/wlwmanifest.xml" => "Attempting to access Wordpress wlwmanifest.xml file.",
        "//cms/wp-includes/wlwmanifest.xml" => "Attempting to access Wordpress wlwmanifest.xml file.",
        "//xmlrpc.php?rsd" => "Suspicous request; //xmlrpc.php?rsd",
        "/manager/text/list" => "Trying to access admin files: /manager/text/list",
        "/boaform/admin/formLogin?username=ec8&psd=ec8" => "Trying to access admin login: /boaform/admin/formLogin",
        "/phpMyAdmin/scripts/setup.php" => "Trying to access phpMyAdmin page.",
        "/TP/public/index.php" => "Tried ThinkPHP Exploit",
        "/phpmyadmin/" => "Tried to access phpMyAdmin page",
        "/clientaccesspolicy.xml" => "Bad web bot, ignores robots.txt",
        "/connector.sds" => "Scanning for vulns, GET /connector.sds",
        "/hudson" => "Scanning for vulns, GET /hudson",
        "/wp-includes/js/jquery/jquery.js" => "Searching for vulns, GET /wp-includes/js/jquery/jquery.js",
        "/webfig/" => "GET /webfig/",
        "/editBlackAndWhiteList" => "GET /editBlackAndWhiteList",
        "/boaform/admin/formLogin?username=adminisp&psd=adminisp" => "Searching for login pages",
        "/wp-admin" => "Looking for wordpress exploits",
        "/index.php?s=/Index/\\think\\app/invokefunction&function=call_user_func_array&vars[0]=md5&vars[1][]=HelloThinkPHP" => "ThinkPHP exploit",
        "/wp-content/plugins/wp-file-manager/readme.txt" => "Searching for Wordpress file manager",
        "/wp/wp-admin/" => "Looking for wordpress admin",
        "/wp-admin/" => "Looking for wordpress admin",
        "/html/public/index.php" => "Looking for framework vulnurabilities",
        "/ab2h" => "Scanning",
        "/solr/" => "Searching for login pages",
        "//sito/wp-includes/wlwmanifest.xml" => "Searching for wordpress files",
        "/PHPMYADMIN/scripts/setup.php" => "phpMyAdmin exploits",
        "/wp-login.php" => "Trying to access wordpress admin page",
        "/wp-config.php" => "Trying to access wordpress files",
        "/ctrlt/DeviceUpgrade_1" => "Router exploit",
        "/nice%20ports%2C/Tri%6Eity.txt%2ebak" => "GET /nice%20ports%2C/Tri%6Eity.txt%2ebak",
        "/wls-wsat/CoordinatorPortType11" => "Oracle WebLogic server Remote Code Execution vulnerability.",
        "/_async/AsyncResponseService" => "Oracle WebLogic server Remote Code Execution vulnerability."
    );
    
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
