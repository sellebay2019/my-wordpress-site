// sw.js — Service Worker for NeuroTechGuide PWA (v4.0)
// Caches core files for offline. Expand for dynamic content.

const CACHE_NAME = 'ntg-v1';
const FILES_TO_CACHE = [
  '/',
  '/index.php',
  '/header.php',
  '/footer.php',
  '/manifest.json',
  '/icon.png',
  // Add more: '/guides/neuralink', etc.
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(FILES_TO_CACHE);
    })
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request).then((response) => {
      return response || fetch(event.request).then((fetchResponse) => {
        return caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, fetchResponse.clone());
          return fetchResponse;
        });
      });
    })
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.filter((name) => name !== CACHE_NAME).map((name) => caches.delete(name))
      );
    })
  );
});