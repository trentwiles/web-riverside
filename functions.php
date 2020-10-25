<?php
namespace RiversideRocks;

require __DIR__ . '/vendor/autoload.php';

class services
{
  public function abuseDB($ip)
  {
    $key = $_ENV["ABUSE_IP_DB"];
    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => 'https://api.abuseipdb.com/api/v2?ipAddress=' . $ip . '&maxAgeInDays=90',
      CURLOPT_USERAGENT => 'Riverside Rocks'
    ]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Accept: application/json',
      'Key: ' . $key
    ));
    $resp = curl_exec($curl);
    curl_close($curl);
    $resp = $ipDetails["data"]["abuseConfidenceScore"];
    die($score > 20 ? "Access Denied" : "Yay! Your IP is ok");
  }
  public function isHacking($path, $ip)
  {
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
  
    $url = $path;

    
  if(isset($hacks[$url])){
      $mes = "AUTOMATED REPORT: " . $hacks[$url];
  }
      
  if(isset($mes)){
      $data = array(
          'ip' => $ip,
          'categories' => '15',
          'comment' => $mes,
      );
        
      $post_data = json_encode($data);
        
      // Prepare new cURL resource
      $crl = curl_init('https://api.abuseipdb.com/api/v2/');
      curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($crl, CURLINFO_HEADER_OUT, true);
      curl_setopt($crl, CURLOPT_POST, true);
      curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
        
      // Set HTTP Header for POST request 
      curl_setopt($crl, CURLOPT_HTTPHEADER, array(
          'Accept' => 'application/json',
          'Key' => $_ENV["ABUSE_IP_DB"]
      ));
        
      $result = curl_exec($crl);
        
      if ($result === false) {
          $result_noti = 0; die();
      } else {
          $result_noti = 1; die();
      }
      curl_close($crl);
      }
  }
}
