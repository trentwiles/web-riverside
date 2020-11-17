<?php

require "vendor/autoload.php";

use DiDom\Document;

$crawl = $_GET["url"];

$cURLConnection = curl_init();
curl_setopt($cURLConnection, CURLOPT_URL, $crawl);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
curl_setopt( $cURLConnection, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Riverside Rocks / Wall Engine; +https://riverside.rocks)" );
$content = curl_exec($cURLConnection);
curl_close($cURLConnection);

$document = new Document($content, true);

$posts = $document->find('a');

foreach($posts as $post) {
    echo $post->text(), "\n";
}