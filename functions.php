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
}
