Pusher.logToConsole = true;

var pusher = new Pusher('d3f96738bc8f4a369b91', {
    cluster: 'us2'
});

var channel = pusher.subscribe(channel_send);
channel.bind('message', function(data) {
    var node = document.createElement("p");
    var textnode = document.createTextNode(data.message);
    node.appendChild(textnode);
    var final = document.getElementById("m")
    final.appendChild(node);
});

/*
Autoscroll service, may be removed in the future once we find a better solution
*/

window.setInterval(function() {
    var elem = document.getElementById('chat');
    elem.scrollTop = elem.scrollHeight;
  }, 10);

function sendMessage(message, key){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange=function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log("OK")
        }else if(this.status == 400){
            document.getElementById("mess").value = "This account is not able to send messages at this time."
        }else{
            console.log("Sorry, something went wrong. Server returned status code of "+this.status)
        }
    };
    xhttp.open("GET", "/v1/new?m="+message+"&key="+key+"&c_id="+channel_send, true);
    xhttp.send();
}
// Example request: https://riverside.rocks/v1/new?m=hello!&key=abcdefg
// If the key is valid, OK should be returned
addEventListener("keyup", function(event) {
    if (event.keyCode === 13) {
        var c = document.getElementById("mess").value
        document.getElementById("mess").value = ""
        sendMessage(c, key)
    }
})

async function uploadFile()
{
    const { value: file } = await Swal.fire({
        title: 'Select image',
        input: 'file',
        inputAttributes: {
          'accept': 'image/*',
          'aria-label': 'Upload something cool'
        }
      })
      
      if (file) {
        const reader = new FileReader()
        reader.onload = (e) => {
          Swal.fire({
            title: 'Your uploaded picture',
            imageUrl: e.target.result,
            imageAlt: 'The uploaded picture'
          })
        }
        reader.readAsDataURL(file)
      }
}