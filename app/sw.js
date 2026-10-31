// Service worker minimal: cukup agar app bisa di-install. Tidak men-cache halaman dinamis.
self.addEventListener('install', function () { self.skipWaiting(); });
self.addEventListener('activate', function (e) { e.waitUntil(self.clients.claim()); });
self.addEventListener('fetch', function () {});
