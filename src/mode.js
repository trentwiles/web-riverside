// "val" should be the value of $_COOKIE["theme"] in PHP
// I don't get the cookie on the client side as cookies in JavaScript can be a bit of a pain
functon getMode($val)
{
  if(val !== "")
  {
    if(val == "light")
    {
      halfmoon.toggleDarkMode(); // While this looks like we are making the theme dark, in reality we are undoing the dark theme.
    }
  }else{
    document.cookie = "theme=dark; expires=Thu, 18 Dec 2073 12:00:00 UTC; path=/"; // Write the cookie that the default mode is dark
  }
}

function updateMode($new) // "new" MUST be set or bad things will hapeen
{
      document.cookie = "theme="+new+"; expires=Thu, 18 Dec 2073 12:00:00 UTC; path=/";
}
