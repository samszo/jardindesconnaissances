
 /*
 Probably one of the simplest functional service workers
 
 Version: 0.0.2
*/
var swDomain = "https://jardindesconnaissances.univ-paris8.fr/jdc/public/";

self.addEventListener('install', e => {
  e.waitUntil(
    caches.open('pwa-assets').then(cache => 
      {
        return cache.addAll([
            swDomain+"css/bootstrap.min.css"
            ,swDomain+"js/d3.v5.min.js"
            ,swDomain+"js/jquery.min.js"
            ,swDomain+"js/site.js"
            ,swDomain+"js/polarclock.js"
            ,swDomain+"js/cartoaxes.js"
            ,swDomain+"font/font-awesome/all.min.js"
            ,swDomain+"css/carousel.css"
            ,swDomain+"css/sonar.css"
            ,swDomain+"sonar/diaporama"
                ])
      })
  );
});

self.addEventListener('fetch', e => {
  e.respondWith(
    caches.match(e.request).then(response => {
      return response || fetch(e.request);
    })
  );
});


