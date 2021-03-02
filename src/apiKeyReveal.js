var key = Cookies.get('key');

function showKey()
{
    Swal.fire(
        'Your API Key',
        key,
        'success'
      )
}