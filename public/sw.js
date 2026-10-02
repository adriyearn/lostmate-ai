/*
 * LostMate AI service worker - makes the app installable and gives it an
 * offline page.
 *
 * What it caches (and what it deliberately does NOT):
 *  - CSS/JS/fonts/icons: cached after first use, so the app opens fast.
 *    Vite gives these unique file names on every build, so a cached copy
 *    is never out of date.
 *  - Pages (HTML): NEVER cached. They contain private data (messages,
 *    hidden details, claims), and on a shared phone or lab PC a cached page
 *    could be shown to the next person. Pages always come from the server;
 *    if there's no connection, the offline page is shown instead.
 *  - Uploaded photos (/storage): not cached, for the same privacy reason.
 */

const CACHE = 'lostmate-static-v1';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE)
            .then((cache) => cache.addAll([OFFLINE_URL, '/icons/icon-192.png']))
            .then(() => self.skipWaiting())
    );
});

// Remove caches from older versions of this file.
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

function isStaticAsset(url) {
    return url.origin === self.location.origin
        ? url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/')
        : url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com';
}

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') {
        return; // form submissions always go straight to the server
    }

    // Page navigations: network only, offline page as the fallback.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    // Static assets: serve from cache, otherwise download and keep a copy.
    const url = new URL(request.url);
    if (isStaticAsset(url)) {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request).then((response) => {
                if (response.ok || response.type === 'opaque') {
                    const copy = response.clone();
                    caches.open(CACHE).then((cache) => cache.put(request, copy));
                }
                return response;
            }))
        );
    }
    // Everything else (photos, polling, etc.) uses the normal network.
});
