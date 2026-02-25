// FaithTunes Service Worker - PWA offline support
var CACHE_NAME = 'faithtunes-v2';
var STATIC_ASSETS = [
  '/',
  '/index.html',
  '/creator.html',
  '/css/app.css',
  '/css/creator.css',
  '/js/app.js',
  '/js/i18n.js',
  '/js/creator.js',
  '/js/consejero.js',
  '/manifest.json'
];

// Install: cache static assets
self.addEventListener('install', function(event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function(cache) {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

// Activate: clean old caches
self.addEventListener('activate', function(event) {
  event.waitUntil(
    caches.keys().then(function(keys) {
      return Promise.all(
        keys.filter(function(k) { return k !== CACHE_NAME; })
            .map(function(k) { return caches.delete(k); })
      );
    })
  );
  self.clients.claim();
});

// Fetch: network first, fallback to cache
self.addEventListener('fetch', function(event) {
  var url = new URL(event.request.url);

  // Skip API calls and external resources - always go to network
  if (url.pathname.startsWith('/api/') || url.origin !== location.origin) {
    return;
  }

  event.respondWith(
    fetch(event.request).then(function(response) {
      // Cache successful responses
      if (response.ok) {
        var clone = response.clone();
        caches.open(CACHE_NAME).then(function(cache) {
          cache.put(event.request, clone);
        });
      }
      return response;
    }).catch(function() {
      // Network failed, try cache
      return caches.match(event.request).then(function(cached) {
        return cached || caches.match('/index.html');
      });
    })
  );
});
