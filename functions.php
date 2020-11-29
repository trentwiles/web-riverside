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
    $num = 1;
    while(true)
    {
      if($api["data"][$num]["users"] == "")
      {
        break;
      }
      else
      {
        $num = $num + 1;
      }
    }
    $magic = $num - 1;
    $users = $api["data"][$magic]["users"];
    $servers = $api["data"][$magic]["servers"];
    $commands = $api["data"][$magic]["commands"];
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

      $json_data = json_encode(array(
          "content" => $mess,
          
          "username" => $name,

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

  /*
   * @param user: username of the github user (we use the same names on riverside.rock as on GitHub)
   * @param eventid: a number created by github (user 0 for latest)
   * @result: array of event details
  */

   public function githubEvent($user, $eventid) // Example: github("RiversideRocks", 0) (Would return latest event for user RiversideRocks)
   {
    $githubSpeak = array(
      "WatchEvent" => "Stared",
      "PushEvent" => "Commited"
    );

     $base = "https://api.github.com/users/" . $user . "/events";

     $json = json_decode(file_get_contents($base), true);
     $ch = curl_init();  
 
     curl_setopt($ch,CURLOPT_URL,$base);
     curl_setopt($ch, CURLOPT_USERAGENT, "RiversideRocks");
     curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  
     $prejson = curl_exec($ch);
  
     curl_close($ch);
     $json = json_decode($prejson, true);

     $og_eventType = $json[$eventid]["type"];
     $eventType = $githubSpeak[$og_eventType];
     $repoName = $json[$eventid]["repo"]["name"];
     $repoUrl = "https://github.com/" . $repoName;
     $eventTime = $json[$eventid]["created_at"];

     $data = array(
       "event" => $eventType,
       "repo" => $repoName,
       "url" => $repoUrl,
       "time" => $eventTime
     );

     return $data;

   }

  /*
   * @param number: number of messages a user has sent
   * @param join: the time in epoch time when a user has joined
   * @result: number of pebbles
  */
   public function calcMsg($number, $join)
   {
     $from_message = $number/0.5;
     $from_join_pre = time() - $join;
     $from_join = $from_join_pre * 0.001;

     return round($from_join + $from_message);
   }
  /*
   * @param number: number of messages a user has sent
   * @param join: the time in epoch time when a user has joined
   * @result: detailed array of the result, good for making a table
  */
   public function calcMsgDetailed($number, $join)
   {
     $from_message = $number/0.5;
     $from_join_pre = time() - $join;
     $from_join = $from_join_pre * 0.001;

     return array("join" => $from_join, "message" => $from_message);
   }
}
