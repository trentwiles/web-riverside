<?php

require "vendor/autoload.php";

use DiDom\Document;
$parser = new \Roboxt\Parser();

# Parse your robots.txt file
$crawl = "https://www.abuseipdb.com/user/47625";

if(! $crawl)
{
    die("Missing URL");
}



$cURLConnection = curl_init();
curl_setopt($cURLConnection, CURLOPT_URL, $crawl);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
curl_setopt( $cURLConnection, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Riverside Rocks / Wall Engine; +https://riverside.rocks)" );
$content = curl_exec($cURLConnection);
curl_close($cURLConnection);

$document = new Document($content);

$posts = $document->find('p');

$links = array();

foreach($posts as $post) {
    array_push($links, $post);
}

print_r($links);

