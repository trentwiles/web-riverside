function confirm()
{
    if(Cookies.get('seen') !== "true"){
        var alertContent = "Thanks for stopping by. If you would like to opt out of our analytics program, please click <a onclick='Cookies.set('lytics', 'false')' class='alert-link'>here.</a>";
        halfmoon.initStickyAlert({
          content: alertContent,      
          title: "Analytics"      
        })
        Cookies.set('seen', 'true')
    }
}

confirm();