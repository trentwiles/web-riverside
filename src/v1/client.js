console.log('%cSTOP!','background: red; color: white; font-size: 78px;');
console.log("%cWARNING! Be careful when using the console.","background: grey; color: white; font-size: 18px;");
console.log("%cBeleive it or not, you can get hacked by pasting malicious code here or by following the directions of a hacker.","background: grey; color: white; font-size: 14px;");console.log("%cOnly use this if you know what you are doing or if you are following the directions of somebody you 110% trust.","background: grey; color: white; font-size: 14px;");console.log("%cIf in doubt, you can always contact me and if you are using the console on your own, you ask questions using our chat (riverside.rocks/app) \n","background: grey; color: white; font-size: 14px;");
Pusher.logToConsole = false;

var pusher = new Pusher('d3f96738bc8f4a369b91', {
    cluster: 'us2'
});

function arrayContains(needle, arrhaystack)
{
    return (arrhaystack.indexOf(needle) > -1);
}

var swears = ['frick'];

var channel = pusher.subscribe(channel_send);
channel.bind('message', function(data) {
    var node = document.createElement("p");
    if(Cookies.get('filter') == "1")
    {
      if(arrayContains(data.message, swears))
      {
        var content = "[ hidden ]"
      }
      else
      {
        var content = data.message;
      }
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

function changeChannel(channel) {
  var url = "https://riverside.rocks/app/channels/"+channel;
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
     document.write(this.responseText);
    }
  };
  xhttp.open("GET", url, true);
  xhttp.send();
  history.pushState({}, null, url);
}

function tosUpdate(){
  if(Cookies.get('seen_tos') !== true)
  {
    Swal.fire(
      'Terms of Service Updated',
      'We recently updated our TOS and Privacy Policy. Please review then <a href="/about/legal">here.</a>',
      'success'
    )
    Cookies.set('seen_tos', '1')
  }
}

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
      'Content Filter Enable',
      'Only safe messages will be shown',
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