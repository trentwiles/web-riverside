Pusher.logToConsole = true;

var pusher = new Pusher('d3f96738bc8f4a369b91', {
    cluster: 'us2'
});

var channel = pusher.subscribe('abuseipdb');
channel.bind('message', function(data) {
    var node = document.createElement("p");
    
    var api_url = "http://ip-api.com/json/"+data.message;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
        var json = this.responseText
        var ip = JSON.parse(json)
        
        var country = ip.country
        var code = ip.countryCode
    }
    };
    xhttp.open("GET", api_url, true);
    xhttp.send();

    var flag = "<img src='https://www.countryflags.io/be/shiny/"+code+"32.png' />";
    var message = flag+"Unauthorized connection attempt detected from "+data.message+" to port 22("+country+")"

    var textnode = document.createTextNode(message);
    node.appendChild(textnode);
    var final = document.getElementById("m")
    final.appendChild(node);
});
