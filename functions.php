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
  public function statcord($id, $ver)
  {
    $base = "https://statcord.com/${ver}/stats/${id}";
    $api = json_decode(file_get_contents($base), true);
    $users = $api["data"][9]["users"];
    $servers = $api["data"][9]["servers"];
    $commands = $api["data"][9]["commands"];
    $array = array($users, $servers, $commands);
    return $array;
  }
  protected function getDiscordAPIurl()
  {
    return $_ENV["DISCORD_WEBHOOK"];
  }
  public function newDiscord($mess, $name)
  {
      if(!isset($mess) || !isset($name))
      {
        throw new Exception("Missing at least one parameter in function newDiscord. This function takes two arguments");
      }
      $webhookurl = $_ENV["DISCORD_WEBHOOK"];

      $timestamp = date("c", strtotime("now"));

      $json_data = json_encode([
          "content" => $mess,
          
          "username" => $name,

          "tts" => false,

      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


      $ch = curl_init( $webhookurl );
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
      curl_setopt( $ch, CURLOPT_POST, 1);
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
      curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt( $ch, CURLOPT_HEADER, 0);
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

      $response = curl_exec( $ch );
      curl_close( $ch );
  }
  public function base64rand($l)
  {
      $chars = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
      $final = "";
      for ($x = 0; $x <= $l; $x++) {
        $number1 = rand(1,62);
        $number2 = $number1 - 1;
        $letter = substr($chars, $number2, $number1);
        $char = $letter[0];
        $final .= $char;
      }
      return $final;
  }
   public function newDiscordContact($content)
   {
      $webhookurl = $_ENV["DISCORD_WEBHOOK_2"];

      $timestamp = date("c", strtotime("now"));

      $json_data = json_encode(array(
          "content" => $content,
          
          "username" => "Message Bot",

          "tts" => false,
          
          "allowed_mentions" => array("parse" => "")
          
          

      ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


      $ch = curl_init( $webhookurl );
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
      curl_setopt( $ch, CURLOPT_POST, 1);
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
      curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt( $ch, CURLOPT_HEADER, 0);
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

      $response = curl_exec( $ch );
      curl_close( $ch );
      return true;
   }
}
