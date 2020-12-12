/* Please note that the following code will only work if the user has the correct auth */

// Move the terminal down
window.setInterval(function() {
    var elem = document.getElementById('chat');
    elem.scrollTop = elem.scrollHeight;
  }, 10);

function send(bash)
{
    $.post(
        "/console",
        {
           com: bash,
        },
        function(data) {
            var node = document.createElement("p");
            var textnode = document.createTextNode(data.message);
            console.log(data.badge);
            node.appendChild(textnode);
            var final = document.getElementById("m")
            final.appendChild(node);
        }
      );
}

addEventListener("keyup", function(event) {
    if (event.keyCode === 13) {
        var c = document.getElementById("mess").value
        document.getElementById("mess").value = ""
        send(c)
    }
})