const CACHE_NAME = 'app-cache';
const FILES_TO_CACHE = [
  '/',
  '/manifest.json',
  '/css/app.css',
  '/js/app.js'
];

self.addEventListener('install', event => {
  console.log('[ServiceWorker] Installed');

  event.waitUntil(
    caches.open(CACHE_NAME).then(async cache => {
      for (const url of FILES_TO_CACHE) {
        try {
          await cache.add(url);
        } catch (error) {
          console.warn(`[ServiceWorker] Skipping failed URL: ${url}`, error);
        }
      }
    })
  );
});
