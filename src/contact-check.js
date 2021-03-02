$("form").submit(function(event) {

    var hcaptchaVal = $('[name=h-captcha-response]').value;
    if (hcaptchaVal === "") {
       event.preventDefault();
       alert("Please complete the hCaptcha");
    }
 });