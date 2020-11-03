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