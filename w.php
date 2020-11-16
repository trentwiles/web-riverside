<?php
require __DIR__ . '/vendor/autoload.php';

use Mike42\Wikitext\WikitextParser as wiki;
$wiki = $_GET["w"];

$wiki_apis = json_decode(file_get_contents("https://wiki.riverside.rocks/api.php?action=query&prop=revisions&titles=${wiki}&rvslots=*&rvprop=content&format=json"), true);

$wiki_content = $wiki_apis["query"]["pages"]["0"]["slots"]["main"]["*"];

$parser = new wiki($wiki_content);
$output = $parser -> result;

echo $wiki_content;