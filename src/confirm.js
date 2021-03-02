// Analytics conset script (by default we collect basic user data, nothing that would )
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
// can also be added to onload