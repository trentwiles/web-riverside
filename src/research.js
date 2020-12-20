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

function res(){
    $( document ).ready(function() {
        $.get("ip.php", function(data, status){
            console.log(status)
            $.get("https://ipapi.co/"+data+"/json/", function(data1, status1){
                alert(data1)
                var ip = JSON.parse(data1)
                var country = ip.country_name
                var ua = navigator.userAgent;
                var ref = document.referrer
                $.post("/v1/research",
                {
                agent: ua,
                locale: country,
                referrer: ref
                },
                function(data3,status3){
                    console.log(status3);
                });
                return true;
            });
        });
    });
}
setInterval(res(), 4000)