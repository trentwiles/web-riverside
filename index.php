<?php
/*
   +----------------------------------------------------------------------+
   | Copyright (c) 2020 Trent Wiles and the Riverside Rocks authors       |
   +----------------------------------------------------------------------+
   | This source file is subject to the Apache 2.0 Lisence.               |
   |                                                                      |
   | If you did not receive a copy of the license and are unable to       |
   | obtain it through the world-wide-web, please send a email to         |
   | support@riverside.rocks so we can mail you a copy immediately.       |
   +----------------------------------------------------------------------+
   | Authors: Trent "Riverside Rocks" Wiles <trent@riverside.rocks>       |
   +----------------------------------------------------------------------+
*/

session_start();
header("X-Powered-By: Riverside Rocks");

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

/*===============================================

EMERGENCY SHUTOFF SWITCH

Upon activation, this will disable global access
to the whole website.

If there is ever a crisis, this can be activitated
internally. While this is "visble" in the source
code, the way to activate the kill switch

================================================*/

if(isset($_ENV["w"]))
{
    die(Phug::displayFile('views/kill-switch-result.pug'));
}

/*===============================================

END EMERGENCY SHUTOFF SWITCH

================================================*/


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

$sql = "SELECT * FROM msg";
    $result = $conn->query($sql);
    $times = 0;
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $times = $times + 1;
        }
    }

    define("mess", $times);


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

$router->get('/about/hacking', function() {
    Phug::displayFile('views/hacking.pug');
});

$router->get('/code/production/cred.js', function() {
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $key = $_COOKIE["key"];
    $sql_key = $conn -> real_escape_string(htmlspecialchars($key));
    $sql = "SELECT * FROM logins WHERE temp_auto_api_key='$sql_key'";
    $result = $conn->query($sql);
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $user = $row["username"];
            break;
        }
    }
    echo "const key = Cookies.get('key');\n";
    echo "const username = \"" . $user . "\";\n";
});

$router->get('/code/production/m.js', function() {
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $one_min_ago = time() - 60;
    $messages = array();
    $sql = "SELECT * FROM logins WHERE epoch > ${one_min_ago}";
    $result = $conn->query($sql);
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            array_push($messages, $row["message"]);
        }
    }
    foreach($messages as $_message){
        $rand = Rocks::base64rand(6);
        echo "var ${rand} = \"" . $_message . "\"; \n";
    }
    print_r($messages); // comment this
});

$router->get('/account/login', function() {
    Phug::displayFile('views/signin.pug');
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

$router->get('/contact', function() {
   header("Location: /about/contact");
});

$router->get('/about/contact', function() {
    Phug::displayFile('views/contact.pug'); // might make this dynamic later, might not
});

$router->post('/about/contact', function() {
   $name = $_POST["name"];
   $email = $_POST["email"];
   $type = $_POST["type"];
   $comment = $_POST["comment"];
   // No need to worry about XSS or SQL injections, thats now Discord's problem hehe
   $final = "From ${name} <${email}> regarding ${type}: **${comment}**";
   Rocks::newDiscord($final, "Mail"); // Note that this will go to the "hacker feed" on my discord server
    print_r($_POST);
   //Phug::displayFile('views/thanks.pug');
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

$router->get('/discord', function() {
    header("Location: https://discord.gg/Pa7S4Hm");
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
    if($_GET["api"] == "true")
    {
        echo $path;
        die();
    }
    $output = $pug->renderFile('views/uploaded.pug', array(
        'url' => $path
    ));
    echo $output;
} catch (\Exception $e) {
    // Fail!
    $errors = $file->getErrors();
    if($_GET["api"] == "true")
    {
        echo $errors;
        die();
    }
    $output = $pug->renderFile('views/upload-fail.pug', array(
        'errors' => $errors
    ));
    echo $output;
}
});

$router->get('/v1/web', function() {
    $pug = new Pug();
    if(! $_SESSION["username"])
    {
        header("Location: /account/login/");
        die();
    }
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $mess = array();
    $users = array();
    $sql = "SELECT * FROM msg ORDER BY `time` DESC";
    $result = $conn->query($sql);
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
                array_push($mess, $row["message"]);
                array_push($users, $row["username"]);
        }
    }
    $output = $pug->renderFile('views/client-v1.pug', array(
        'username' => $_SESSION["username"],
        'id' => $_SESSION["id"],
        'mes1' => $mess[0],
        'user1' => $users[0],
        'mes2' => $mess[1],
        'user2' => $users[1],
        'mes3' => $mess[2],
        'user3' => $users[2],
        'mes4' => $mess[3],
        'user4' => $users[3],
        'debug' => ""
    ));
    echo $output;
});

$router->get('/v1/new', function() {
    $options = array(
        'cluster' => 'us2',
        'useTLS' => true
    );
    $pusher = new Pusher\Pusher(
        $_ENV["PUSHER_APP_KEY"],
        $_ENV["PUSHER_APP_SECRET"],
        $_ENV["PUSHER_APP_ID"],
        $options
    );
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sent_api_key = $conn -> real_escape_string($_GET["key"]);
    $sql = "SELECT * FROM logins WHERE temp_auto_api_key='$sent_api_key'";
    $result = $conn->query($sql);

    if(!$_GET["key"])
    {
        die("400 Bad Request");
    }

    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $_username = $row["username"];
            break;
        }
    }

    if(!isset($_username))
    {
        die("400 Bad Request");
    }

    $data['message'] = $_username . ": " . htmlspecialchars($_GET["m"]);
    $pusher->trigger('general', 'message', $data);

    $new = $_username;

    $_user = $conn -> real_escape_string(htmlspecialchars($new));
    $_mes = $conn -> real_escape_string(htmlspecialchars($_GET["m"]));
    $_time = $conn -> real_escape_string(htmlspecialchars(time()));
    $_mess_id = $_time . rand() . rand();

    $sent_api_key = $conn -> real_escape_string($data['message']);
    if($_user == "tucker")
    {
        die("400");
    }
    if(!isset($_user))
    {
        die("400 Bad Request");
    }

    $sql = "INSERT INTO `msg` (`username`, `message`, `time`, `mess_id`) VALUES ('${_user}', '${_mes}', '${_time}', '${_mess_id}')";
    $result = $conn->query($sql);
    echo "OK";
});



$router->get('/community', function() {
    Phug::displayFile('views/community-temp.pug');
});

$router->get('/users/(\w+)', function($id) {
    $pug = new Pug();
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $discord = $conn -> real_escape_string($id);
    $sql = "SELECT * FROM logins WHERE username='${discord}'";
    $result = $conn->query($sql);
    $times = 0;
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $user = htmlspecialchars($row["username"]);
            if($row["username"] == "")
            {
                die(Phug::displayFile('views/user-404.pug'));
            }
            $bio = htmlspecialchars($row["bio"]);
            if(! $bio)
            {
                $bio = "Looks like this user hasn't set a bio!";
            }
            $pre_join = $row["epoch"];
            $join = date("m-d-Y H:i:s", $pre_join);
        }
    }
    $output = $pug->render('views/user.pug', array(
        'username' => $user,
        'bio' => $bio,
        'join' => $join
    ));
    echo $output;
});

$router->get('/oauth/github', function() {
    $provider = new League\OAuth2\Client\Provider\Github([
        'clientId'          => $_ENV["GITHUB_CLIENT"],
        'clientSecret'      => $_ENV["GITHUB_SECRET"],
        'redirectUri'       => $_ENV["GITHUB_CALLBACK"],
    ]);
    
    if (!isset($_GET['code'])) {
    
        // If we don't have an authorization code then get one
        $authUrl = $provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: '.$authUrl);
        exit;
    
    // Check given state against previously stored one to mitigate CSRF attack
    } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    
        unset($_SESSION['oauth2state']);
        exit('Invalid state');
    
    } else {
    
        // Try to get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
    
        // Optional: Now you have a token you can look up a users profile data
        try {
    
            // We got an access token, let's now get the user's details
            $user = $provider->getResourceOwner($token);
            // Use these details to create a new profile
            $github_username = htmlspecialchars($user->getNickname());
            $github_id = htmlspecialchars($user->getId());
            $_SESSION["username"] = $github_username;
            $_SESSION["id"] = $github_id;
            $github_time = time();
            

            /*==========================================
            Insert or Update the Database
            ===========================================*/
            $servername = $_ENV['MYSQL_SERVER'];
            $username = $_ENV["MYSQL_USERNAME"];
            $password = $_ENV["MYSQL_PASSWORD"];
            $dbname = $_ENV["MYSQL_DATABASE"];

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            $remote_ip = $conn -> real_escape_string(htmlspecialchars($_SERVER['REMOTE_ADDR']));
            $user_agent = $conn -> real_escape_string(htmlspecialchars($_SERVER['HTTP_USER_AGENT']));

            echo "Hello ${github_username}, your ID is ${github_id}";
            $sql = "SELECT * FROM logins WHERE username='${github_username}'";
            $result = $conn->query($sql);
            if (!empty($result) && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Erase old logins
                    $sql = "DELETE FROM logins WHERE username='${github_username}'";
                    $result = $conn->query($sql);
                    break;
                }
            }

            $temp_auto_api_key = Rocks::base64rand(30);
            $cookie_name = "key";
            $cookie_value = $temp_auto_api_key;
            setcookie($cookie_name, $cookie_value, time() + (864000 * 30), "/"); // 10 days, might change this in the future
            $sql = "INSERT INTO `logins`(`IP`, `agent`, `human_agent`, `username`, `id`, `login_time`, `temp_auto_api_key`) VALUES ('${remote_ip}', '${user_agent}', 'Not Found', '${github_username}', '${github_id}', '${github_time}', '${temp_auto_api_key}')";
            $result = $conn->query($sql);

            $sql = "SELECT * FROM bans";
            $result = $conn->query($sql);
            if (!empty($result) && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    if($row["id"] == $github_id)
                    {
                        session_start();
                        session_unset();
                        session_destroy();
                        $pug = new Pug();
                        $output = $pug->renderFile('views/banned.pug', array(
                            'rule' => htmlspecialchars($row["rule"]),
                            'note' => htmlspecialchars($row["note"]),
                            'type' => htmlspecialchars($row["type"])
                        ));
                        echo $output;
                        die();
                    }
                }
            }

            header("Location: /account/dashboard");
            die();
    
        } catch (Exception $e) {
    
            // Failed to get user details
            exit('Oh dear...');
        }
    
        // Use this to interact with an API on the users behalf
        //echo $token->getToken();
    }
});
$router->get('/admin', function() {
    header("Location: /admin/upload/");
    die();
});


$router->get('/account/dashboard', function() {
    $pug = new Pug();
    $output = $pug->renderFile('views/dashboard.pug', array(
        'username' => $_SESSION["username"],
        'icon' => "https://avatars0.githubusercontent.com/u/" . $_SESSION["id"],
    ));
    echo $output;
    // Note to self, work on this!
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
        $to_discord = "${ip} - ${mes}";
        Rocks::newDiscord($to_discord, "Hacker Feed");
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
    // If a hacking attempt is detected, we show the 403 page
    if(in_array($_SERVER["REQUEST_URI"], $hacks))
    {
        Phug::displayFile('views/403.pug');
    }
    else
    {
        Phug::displayFile('views/404.pug');
    }
    $list = Secure::userAgents();
    if(in_array($ua, $list))
    {
        $mes = $list[$ua];
        if(isset($mes)){
            $ip = $_SERVER['REMOTE_ADDR'];
            $to_discord = "${ip} - ${mes}";
            Rocks::newDiscord($to_discord, "Hacker Feed");
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

$router->run();
