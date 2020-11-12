# Riverside Rocks
Website for Riverside Rocks. Powered by Bramus router, Pug, Pusher, and PHP 7.

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

*\* key is required and the site will not function without it*

If you need some help, please check out our [example .env file.](https://github.com/RiversideRocks/web-riverside/blob/master/.env-example)

You are ready to go!
