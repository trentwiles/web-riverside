<?php

require "vendor/autoload.php";

use Spatie\Crawler\Crawler;

Crawler::create()
    ->startCrawling("https://riverside.rocks");