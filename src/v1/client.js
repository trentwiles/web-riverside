Pusher.logToConsole = true;

var pusher = new Pusher('d3f96738bc8f4a369b91', {
    cluster: 'us2'
});

var channel = pusher.subscribe('general');
channel.bind('message', function(data) {
    var node = document.createElement("p");
    var textnode = document.createTextNode(data.message);
    node.appendChild(textnode);
    document.getElementById("m").appendChild(node);
});

function sendMessage(message, key){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange=function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log("OK")
        }else{
            console.log("Sorry, something went wrong. Server returned status code of "+this.status)
        }
    };
    xhttp.open("GET", "/v1/new?m="+message+"&key="+key, true);
    xhttp.send();
}
// Example request: https://riverside.rocks/v1/new?m=hello!&key=abcdefg
// If the key is valid, OK should be returned
function form() {
    var content = document.getElementById("mess").value
    sendMessage(content, key);
    document.getElementById("mess") = ""
}