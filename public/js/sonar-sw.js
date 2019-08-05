
 /*
 Probably one of the simplest functional service workers
 
 Version: 0.0.2
*/

self.addEventListener('install', e => {
  e.waitUntil(
    caches.open('pwa-assets').then(cache => 
      {
        return cache.addAll([
            "../css/bootstrap.min.css"
            ,"../js/d3.v5.min.js"
            ,"../js/jquery.min.js"
            ,"../js/site.js"
            ,"../js/polarclock.js"
            ,"../js/cartoaxes.js"
            ,"../font/font-awesome/all.min.js"
            ,"../css/carousel.css"
            ,"../css/sonar.css"
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
