<?php

namespace RiversideRocks;

class services
{
  public function abuseDB($ip)
  {
    $client = new GuzzleHttp\Client([
      'base_uri' => 'https://api.abuseipdb.com/api/v2/'
    ]);

    $response = $client->request('GET', 'check', [
      'query' => [
          'ipAddress' => $ip,
          'maxAgeInDays' => '90',
    ],
    'headers' => [
        'Accept' => 'application/json',
        'Key' => $_ENV["ABUSE_IP_DB"]
     ],
    ]);

    $output = $response->getBody();
    $ipDetails = json_decode($output, true);
    $score = $ipDetails["data"]["abuseConfidenceScore"];
    die($score > 20 ? "Access Denied" : "Yay! Your IP is ok");
  }
}
