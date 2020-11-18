<?php

require "vendor/autoload.php";

use DiDom\Document;
$parser = new \Roboxt\Parser();

die("Closed to the public");
# Parse your robots.txt file
$crawl = $_GET["url"];
$page = $_GET["page"];

if(! $crawl)
{
    die("Missing URL");
}

if(! $page)
{
    die("Missing URL");
}

$file = $parser->parse($crawl . "/robots.txt");


$cURLConnection = curl_init();
curl_setopt($cURLConnection, CURLOPT_URL, $crawl);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
curl_setopt( $cURLConnection, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Riverside Rocks / Wall Engine; +https://riverside.rocks)" );
$content = curl_exec($cURLConnection);
curl_close($cURLConnection);

$document = new Document($content);

$posts = $document->find('a');

$links = array();

foreach($posts as $post) {
    array_push($links, $post->getAttribute('href'));
}

