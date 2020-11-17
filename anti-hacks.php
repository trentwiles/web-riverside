<?php
require __DIR__ . '/vendor/autoload.php';

require 'functions.php';
require 'security.php';

$rocks = new \RiversideRocks\services;
$secure = new \RiversideRocks\security;

$router = new \Bramus\Router\Router();
$pug = new Pug();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo "Under header <br>";
    $hacks = $secure->returnExploits();
    
    $url = $_SERVER["REQUEST_URI"];
    
    $user_agent_blacklist = array(
        "Go-http-client/1.1",
        "Mozilla/5.0 zgrab/0.x",
        "python-requests/2.24.0"
    );
    
    $ua = $_SERVER['HTTP_USER_AGENT'];
    echo "Before issets <br>";
    if(isset($hacks[$url]) || isset($baduseragent[$ua])){
        $mes = "AUTOMATED REPORT: " . $hacks[$url];
    }
    
    if(isset($baduseragent[$ua])){
        $mes = "AUTOMATED REPORT: Port Scanning: " . $url;
    }
        echo "Before report <br>";
    if(isset($mes)){
        $ip = $_SERVER['REMOTE_ADDR'];
        $to_discord = "${ip} - ${mes}";
        $rocks->newDiscord($to_discord, "Hacker Feed");
        $client = new GuzzleHttp\Client([
            'base_uri' => 'https://api.abuseipdb.com/api/v2/'
          ]);
          echo "Before POST <br>";
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
          echo "After send <br>";
          $output = $response->getBody();
          // Store response as a PHP object.
          $ipDetails = json_decode($output, true);
          echo "after full report <br>";
          $servername = $_ENV['MYSQL_SERVER'];
          $username = $_ENV["MYSQL_USERNAME"];
          $password = $_ENV["MYSQL_PASSWORD"];
          $dbname = $_ENV["MYSQL_DATABASE"];

          $conn = new mysqli($servername, $username, $password, $dbname);
          echo "Before db <br>";
          if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
          }
          $sql = "INSERT INTO `blocklist`(`ip`, `reason`) VALUES ('${ip}', 'Hacking attempt (HTTP)')";
          $result = $conn->query($sql);
          echo "After DB <br>";
          
        }

        echo "0_0";
    $list = $secure->userAgents();
    if(in_array($ua, $list))
    {
        $mes = $list[$ua];
        if(isset($mes)){
            $ip = $_SERVER['REMOTE_ADDR'];
            $to_discord = "${ip} - ${mes}";
            $rocks->newDiscord($to_discord, "Hacker Feed");
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
    }
});
