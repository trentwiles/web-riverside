/*
   +----------------------------------------------------------------------+
   | Copyright (c) 2020 Riverside Rocks authors                           |
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

/*
* @returns Client's IP
*/

function getIP(){
    //
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            return this.responseText 
        }
    }
    xhttp.open("GET", "https://riverside.rocks/ip", true);
    xhttp.send();
}

/*
* @param Client's IP
* @returns Client's Country
*/

function getCountryFromIP(ip){
    var api_url = "https://ipapi.co/"+ip+"/json/";
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            const jsonip = this.responseText 
            var ip = JSON.parse(jsonip)
            var country = ip.country_name
            return country;
        }
    }
    xhttp.open("GET", api_url, true);
    xhttp.send();
}

function send()
{
    var ua = navigator.userAgent;
    var country = getCountryFromIP(getIP())
    var ref = document.referrer
    $.post("/v1/research",
    {
    agent: ua,
    locale: country,
    referrer: ref
    },
    function(data,status){
        console.log(status);
    });
}

setInterval(send(), 4000)