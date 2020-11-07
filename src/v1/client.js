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
    $('#chat').scrollTop($('#chat')[0].scrollHeight);
});

function sendMessage(message, key){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange=function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log("OK")
        }else if(this.status == 400){
            document.getElementById("mess").value = "This account is not able to send messages."
        }else{
            console.log("Sorry, something went wrong. Server returned status code of "+this.status)
        }
    };
    xhttp.open("GET", "/v1/new?m="+message+"&key="+key, true);
    xhttp.send();
}
// Example request: https://riverside.rocks/v1/new?m=hello!&key=abcdefg
// If the key is valid, OK should be returned
addEventListener("keyup", function(event) {
    if (event.keyCode === 13) {
        var c = document.getElementById("mess").value
        sendMessage(c, key)
        document.getElementById("mess").value = ""
    }
})
