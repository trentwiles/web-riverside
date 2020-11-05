# Riverside Rocks
Website for Riverside Rocks.

Live at:

https://riverside.rocks

## Requirements

- PHP 7 or higher (our server runs PHP 7.3 at the moment)
- Apache2 (Nginx *should* work, but I've never tried it myself.)
- MySQL

## Installing

Begin by cloning the repo:

`git clone https://github.com/RiversideRocks/web-riverside.git`

Install all dependencies:

`composer install`

## Setting up API keys and secrets

Currently our .env file accepts:

- MySQL server *
- MySQL username *
- MySQL password *
- MySQL database *
- Google API key with access to the youtube API *
- AbuseIPDB API key
- Uploading key, a password used to upload assets to the site
- Github Oauth Secret
- Github Oauth Client
- Github Callback
- Pusher API Key

*A start indicates that the key is required and the site will not function without it.*

You are ready to go!
