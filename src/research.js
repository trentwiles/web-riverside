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
    //if(Cookies.read("opt") !== "true")
        //{
        $( document ).ready(function() {
            $.get("/ip.php", function(data, status){
                console.log(status)
                $.get("https://ipapi.co/"+data+"/country/", function(data1, status1){
                    var country = data1
                    var ua = navigator.userAgent;
                    var ref = document.referrer
                    if(ref == "")
                    {
                        ref = "None"
                    }
                    $.post("/v1/research",
                    {
                    agent: ua,
                    locale: country,
                    time: Date.now(),
                    referrer: ref
                    },
                    function(data3,status3){
                        console.log(status3);
                        console.log(data3);
                    });
                    //return true;
                });
            });
            if(username !== "")
            {
                var isAuth = true;
                $.post("/v1/boost",
                {
                    isClientAuth: isAuth
                },
                function(data3,status3){
                    console.log(status3);
                    console.log(data3);
                });
            }
        });
    //}else{
      //  console.log("We noticed that you opted out of analytics. We respect that. (╯°□°）╯︵ ┻━┻")
    //}
}
