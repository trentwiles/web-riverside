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

//die("Offline for matinence, sorry. Contact trent@riverside.rocks for assistance.");

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
use IPTools\Network;

$exploits = Secure::returnExploits();

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
        "message" => "Unauthorised Connection: 403 Forbidden",
        "reason" => htmlspecialchars($reason),
        "server" => "358 Engine"
    );
    $return = json_encode($reasons, true);
    die($return);
}




$ipinfo = json_decode(file_get_contents("http://ip-api.com/json/${ip}"), true);
$country = $conn -> real_escape_string(htmlspecialchars($ipinfo["country"]));
$epoch = time();

$stmt = $conn->prepare("INSERT INTO logs (epoch, country) VALUES (?, ?)");
$stmt->bind_param("is", $epoch1, $country1);

$epoch1 = $epoch;
$country1 = $country;
$stmt->execute();
$stmt->close();

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

$router->get('/about/feed', function() {
    Phug::displayFile('views/ip.pug');
});

$router->get('/api/bycountry', function() {
    print_r($countries);
});

$router->get('/api/cidr', function() {
    $hosts = Network::parse('1.1.1.0/24')->hosts; // Range(192.168.1.1, 192.168.1.254);
    foreach($hosts as $ip) {
        echo (string)$ip . '<br>';
    }
});


/*===========================
/\/\/\/\/\/\/\/\/\/\/\/\/\/\
END EXPERIMENTAL API ENDPOINTS
/\/\/\/\/\/\/\/\/\/\/\/\/\/\
===========================*/


$router->get('/', function() {
    $pug = new Pug();
    /*
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $channel_id = "UCoHNPdbSrE2c_g95JgGiBkw";
    $api_key = $_ENV["YOUTUBE"];
    $api_response = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=statistics&id='.$channel_id.'&fields=items/statistics/subscriberCount&key='.$api_key);
    $api_response_decoded = json_decode($api_response, true);
    $subs = $api_response_decoded['items'][0]['statistics']['subscriberCount'];
    $api_response2 = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=statistics&id='.$channel_id.'&fields=items/statistics/viewCount&key='.$api_key);
    $api_response_decoded2 = json_decode($api_response2, true);
    $views = $api_response_decoded2['items'][0]['statistics']['viewCount'];
    */
    if($_SESSION["username"] !== "")
    {
        $authuser = htmlspecialchars($_SESSION["username"]);
        $profile = "https://riverside.rocks/users/" . $authuser;
    }
    /*
    $latest = Rocks::githubEvent("RiversideRocks", 0);
    $github = "Latest from GitHub: " . $latest["event"] . " (Repo " . $latest["repo"] . ")";
    */
    $output = $pug->render('views/index.pug', array(
        'user' => $authuser,
        'profile' => $profile,
    ));
    echo $output;
});


$router->get('/about', function() {
    $pug = new Pug();
    $ipdb = file_get_contents("https://riverside.rocks/crawl.php");
    $output = $pug->render('views/about.pug', array(
        'ipdb' => $ipdb,
    ));
    Phug::displayFile('views/about.pug');
});

$router->get('/about/legal', function() {
    Phug::displayFile('views/legal.pug');
});

$router->get('/about/hacking', function() {
    Phug::displayFile('views/hacking.pug');
});

$router->get('/apps/abuseipdb', function() {
    Phug::displayFile('views/abuseipdb.pug');
});

$router->post('/apps/abuseipdb', function() {
    $abuseipdb_id = $_POST["id"];
    $reports = file_get_contents("https://riverside.rocks/crawl.php?id=" . $abuseipdb_id);
    $pug = new Pug();
    $output = $pug->renderFile('views/abuseipdb.pug', array(
        'reports' => $reports,
    ));
    echo $output;
});


$router->get('/code/production/cred.js', function() {
    header("Content-type: text/javascript");
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

    $sql = "SELECT * FROM logins WHERE temp_auto_api_key=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $sql_key);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $user = $row["username"];
        break;
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
   $data = array(
    'secret' => $_ENV["CAPTCHA"],
    'response' => $_POST['h-captcha-response']
    );
    $verify = curl_init();
    curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
    curl_setopt($verify, CURLOPT_POST, true);
    curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($verify);
    $responseData = json_decode($response, true);
    if($responseData["success"]) {
        Rocks::newDiscordContact($final);
        Phug::displayFile('views/thanks.pug');
    } 
    else {
        header("HTTP/1.1 403 Forbidden");
        echo "You did not pass the captcha.";
    }
   
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
    die(header("Location: https://www.youtube.com/RiversideRocks"));

    // This is all ignored for now...
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

    $stmt = $conn->prepare("INSERT INTO uploads (`url`, epoch) VALUES (?, ?)");
    $stmt->bind_param("si", $path0, $epoch0);

    $path0 = $path;
    $epoch0 = $epoch;
    $stmt->execute();
    $stmt->close();
    
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

$router->get('/app/beta', function() {
    Phug::displayFile('views/beta.pug');
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
        'url_custom' => "https://riverside.rocks/app/channels/pm/" . $gen_id
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


    $sql = "SELECT * FROM msg WHERE `channel`=? ORDER BY `time` DESC";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $channel_sql);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        array_push($mess, $row["message"]);
        array_push($users, $row["username"]);
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

$router->get('/app/channels/pm/(\w+)', function($channel) {
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
        header("Location: /app/create");
        die();
    }
    $_SESSION["channel"] = $channel_select;
    $mess = array();
    $users = array();
    $channel_sql = $conn -> real_escape_string(htmlspecialchars($channel));
    $sql = "SELECT * FROM msg WHERE `channel`=? ORDER BY `time` DESC";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $channel_sql);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        array_push($mess, $row["message"]);
        array_push($users, $row["username"]);
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
    $sql = "SELECT * FROM logins WHERE temp_auto_api_key=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $sent_api_key);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $_username = $row["username"];
        break;
    }
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
    $sql = "SELECT * FROM admins WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $_username);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if($row["username"] == $_username)
        {
            $_badge = 'fas fa-shield-alt'; // for the moment we have admin off due to pusher escaping html
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
    $sql = "SELECT * FROM `msg` WHERE username=? AND `time` > ?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("si", $_user, $runtime);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $rate = $rate + 1;
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

$router->get('/app/support', function() {
    Phug::displayFile('views/client-support.pug');
});


$router->get('/account/signout', function() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: /");
    die();
});

$router->get('/blog/(\w+)', function($slugd) {
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if($slugd == "")
    {
        echo "Blog Homepage Should be here sooner than later...";
    }
    $slug = htmlspecialchars($slugd);
    $pug = new Pug();
    $sql = "SELECT * FROM blog WHERE slug=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $html = $row["html"];
        $author = htmlspecialchars($row["author"]);
        $time = $row["time"];
        $title = htmlspecialchars($row["title"]);
        $tag = htmlspecialchars($row["tag"]);
        $tag_url = "https://riverside.rocks/blog/tag/" . strtolower($tag);
        $twitter = "https://twitter.com/intent/tweet?url=https://riverside.rocks/blog/" . $slug . "/&text=Interesting post from Riverside Rocks! Check it out!";
    }
    if($title !== "")
    {
        header("Location: /blog/");
        die();
    }
    $output = $pug->render('views/blog.pug', array(
        'post' => $html,
        'title' => $title,
        'author' => $author,
        'date' => date("m-d-Y", $time),
        'tagurl' => $tag_url,
        'tag' => $tag
    ));
    echo $output;
});

$router->get('/account/dashboard/admin', function() {
    $pug = new Pug();
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $discord = $_SESSION["username"];
    $sql = "SELECT * FROM admins WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $discord);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if(! $row["username"])
        {
            header("Location: /dashboard");
            die();
        }
    }
    $output = $pug->render('views/admin-home.pug', array(
        'username' => htmlspecialchars($discord),
    ));
    echo $output;
});

$router->get('/account/dashboard/admin/action', function() {
    $pug = new Pug();
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $discord = $_SESSION["username"];
    $sql = "SELECT * FROM admins WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $discord);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if(! $row["username"])
        {
            header("Location: /account/dashboard");
            die();
        }
    }
    $output = $pug->render('views/admin-select.pug', array());
    echo $output;
});

$router->get('/account/dashboard/admin/action/(\w+)', function($user_name) {
    $pug = new Pug();
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $discord = $_SESSION["username"];
    $sql = "SELECT * FROM admins WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $discord);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if(! $row["username"])
        {
            header("Location: /dashboard");
            die();
        }
    }

    $sql = "SELECT * FROM logins WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if(! $row["username"])
        {
            die(header("Location: /account/dashboard/admin/action/"));
        }
        else
        {
            $ip = $row["ip"];
        }
    }
    
    $output = $pug->render('views/admin-action.pug', array(
        'user' => htmlspecialchars($user_name),
        'ip' => htmlspecialchars($ip)
    ));
    echo $output;
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
        "pebble_url" => $pebble_url
    ));
    echo $output;
});

$router->get('/users/(\w+)/pebbles', function($id) {
    $pug = new Pug();
    $servername = $_ENV['MYSQL_SERVER'];
    $username = $_ENV["MYSQL_USERNAME"];
    $password = $_ENV["MYSQL_PASSWORD"];
    $dbname = $_ENV["MYSQL_DATABASE"];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT * FROM msg WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count4 = 0;
    while ($row = $result->fetch_assoc()) {
        $count4 = $count4 + 1;
    }

    $sql = "SELECT * FROM logins WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pre_join = $row["login_time"];
        $user = htmlspecialchars($row["username"]);
    }
    if($user == ""){
        die(header("Location: /request-error?code=404"));
    }
    $sql = "SELECT * FROM msg WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count4 = 0;
    while ($row = $result->fetch_assoc()) {
        $count4 = $count4 + 1;
    }

    $pebbles = Rocks::calcMsgDetailed($count4, $pre_join);
    $full = Rocks::calcMsg($count4, $pre_join);
    $output = $pug->render('views/breakdown.pug', array(
        "message" => $pebbles["message"],
        'join' => $pebbles["join"],
        'total' => $full,
        "username" => htmlspecialchars($id)
    ));

    echo $output;

});

$router->get('/blog', function($id) {
    Phug::displayFile('views/blog-home.pug');
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

            $sql = "SELECT * FROM logins WHERE username=?";
            $stmt = $conn->prepare($sql); 
            $stmt->bind_param("s", $github_username);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                if($row["bio"] == "")
                {
                    $show_onboarding = "true";
                }
                $following = $row["following"];
                $followers = $row["followers"];
                $sql = "DELETE FROM logins WHERE username=?";
                $stmt = $conn->prepare($sql); 
                $stmt->bind_param("s", $github_username);
                $stmt->execute();
                break;
            }

            $following = "[]";

            $followers = "[]";
            
            //die($followers . $following);

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
            $sql = "INSERT INTO `logins`(`IP`, `agent`, `human_agent`, `username`, `id`, `bio`, `login_time`, `temp_auto_api_key`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql); 
            $human_readable = "Not found";
            $stmt->bind_param("ssssisis", $remote_ip, $user_agent, $human_readable, $github_username, $github_id, $bio, $github_time, $temp_auto_api_key);
            $stmt->execute();
            
            echo "\n DEBUG: INSERT NEW RECORD (IF YOU ARE SEEING THIS SCREEN, PLEASE EMAIL TRENT@RIVERSIDE.ROCKS \n";

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

$router->get('/legal', function() {
    header("Location: /about/legal/");
    die();
});

$router->get('/start', function() {
    header("Location: /app/");
    die();
});

$router->get('/why', function() {
    header("Location: /about/");
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
    $sql = "SELECT * FROM logins WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $github_username);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $bio_pre = htmlspecialchars($row["bio"]);
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
    $sql = "UPDATE `logins` SET `bio`=? WHERE username=?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("ss", $updated_bio, $auth_u);
    $stmt->execute();
    header("Location: /account/dashboard");
    die();
});

$router->get('/wp-login.php', function() {
   $pug = new Pug();
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
                      'comment' => "Attempted to access wordpress admin page"
                  ],
                  'headers' => [
                      'Accept' => 'application/json',
                      'Key' => $_ENV["ABUSE_IP_DB"]
                ],
              ]);
              
              $output = $response->getBody();
              // Store response as a PHP object.
              $ipDetails = json_decode($output, true);
   $output = $pug->renderFile('views/wp-login.pug', array());
    echo $output;
});

$router->post('/wp-login.php', function() {
   $pass = $_POST["pwd"];
   switch(strpos($pass, "@everyone"))
   {
       case true:
        Rocks::newDiscord("Would you look at that, someone tried to ping the whole server.", "Idiot");
        die();
   }
   switch(strpos($pass, "@here"))
   {
       case true:
        Rocks::newDiscord("Would you look at that, someone tried to ping the whole server.", "Idiot");
        die();
   }
   $log_m = 
   "
   Attempted to hack into wordpress admin:
   
   **Password:** ${pass}
   ";
   Rocks::newDiscord($log_m, "Wordpress Hacker");
   header("Location: /wp-login.php");
   die();
});

$router->get('/wp-admin/', function() {
   header("Location: /wp-login.php");
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
        }
          //$output = $response->getBody();
          // Store response as a PHP object.
          //$ipDetails = json_decode($output, true);
          $servername = $_ENV['MYSQL_SERVER'];
          $username = $_ENV["MYSQL_USERNAME"];
          $password = $_ENV["MYSQL_PASSWORD"];
          $dbname = $_ENV["MYSQL_DATABASE"];

          $conn = new mysqli($servername, $username, $password, $dbname);
          if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
          }
          //$sql = "INSERT INTO `blocklist` (`ip`, `reason`) VALUES ('${ip}','Hacking attempt (HTTP)')";
          //$result = $conn->query($sql);
        //$data['http'] = $ip;
       //$pusher = new Pusher\Pusher(
        //$_ENV["PUSHER_APP_KEY"],
        //$_ENV["PUSHER_APP_SECRET"],
        //$_ENV["PUSHER_APP_ID"],
        //$options
    //);
            //$pusher->trigger('abuseipdb', 'http', $data);
        
   
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
   header("HTTP/1.1 404 Not Found");
});



$router->run();
