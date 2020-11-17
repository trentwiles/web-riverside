Pusher.logToConsole = true;

var pusher = new Pusher('d3f96738bc8f4a369b91', {
    cluster: 'us2'
});

function arrayContains(needle, arrhaystack)
{
    return (arrhaystack.indexOf(needle) > -1);
}

var channel = pusher.subscribe(channel_send);
channel.bind('message', function(data) {
    var node = document.createElement("p");
    if(Cookies.get('filter') == "1")
    {
      if(arrayContains(data.message, )
    }
    var textnode = document.createTextNode(data.message);
    console.log(data.badge);
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
        }else if(this.status == 401){
          Swal.fire(
            'Warning!',
            'It looks like you are using an invalid API token. Please consider signing back in.',
            'error'
          )
        }else if(this.status == 400){
          Swal.fire(
            'Something went wrong',
            'Our API could not process your request. Maybe you sent a blank message or a message over 500 characters?',
            'error'
          )
        }else if(this.status == 429){
          Swal.fire(
            'Woah! Slow down!',
            'You are sending way too many messages!',
            'error'
          )
        }else if(this.status == 403){
          location.reload(); 
        }else{
            console.log(this.status)
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
          $.post("/v1/ugc-handler",
          {
            img: e.target.result
          },
          function(data,status){
          Swal.fire({
            title: 'Check this out!',
            text: '<img src="'+data+'" />',
          });
          });
        }
        reader.readAsDataURL(file)
      }
}

function checkFilter()
{
  if(Cookies.get('filter') == "" || Cookies.get('filter') == "0")
  {
    Cookies.set('filter', '1')
    Swal.fire(
      'Content Filter Enabled',
      'Content filtering is now enabled. Messages with foul language will be censored.',
      'success'
    )
  }
  else
  {
    Cookies.set('filter', '0')
    Swal.fire(
      'Content Filter Disbaled',
      'All messages will be shown',
      'success'
    )
  }
}