if('serviceWorker' in navigator) {
  navigator.serviceWorker
           .register('/pwa/sw.js')
           .then(function() { console.log("Service Worker Registered"); });
}
