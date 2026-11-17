const CACHE = "iprojects-v15";
const OFFLINE_URL = "/offline.html?v=15";
const SHELL = [OFFLINE_URL, "/manifest.webmanifest", "/icons/ip-192.png", "/icons/ip-32.png"];

self.addEventListener("install", function (event) {
    event.waitUntil(
        caches.open(CACHE).then(function (cache) { return cache.addAll(SHELL); }).catch(function () {})
    );
    self.skipWaiting();
});

self.addEventListener("activate", function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(keys.filter(function (k) { return k !== CACHE; }).map(function (k) { return caches.delete(k); }));
        }).then(function () { return self.clients.claim(); })
    );
});

self.addEventListener("fetch", function (event) {
    const request = event.request;

    if (request.method !== "GET") return;

    if (request.mode === "navigate") {
        event.respondWith(
            fetch(request).catch(function () {
                return caches.match(OFFLINE_URL).then(function (res) {
                    return res || new Response("Offline", { status: 503, headers: { "Content-Type": "text/plain" } });
                });
            })
        );
        return;
    }

    const url = new URL(request.url);
    if (url.origin !== location.origin) return;

    if (url.pathname.startsWith("/icons/") || url.pathname === "/manifest.webmanifest") {
        event.respondWith(
            caches.match(request).then(function (cached) {
                return cached || fetch(request).then(function (res) {
                    const copy = res.clone();
                    caches.open(CACHE).then(function (cache) { cache.put(request, copy); });
                    return res;
                });
            })
        );
    }
});

self.addEventListener("push", function (event) {
    let data = {};
    try { data = event.data ? event.data.json() : {}; } catch (e) { data = { body: event.data ? event.data.text() : "" }; }

    const title = data.title || "iProjects";
    const options = {
        body: data.body || "",
        icon: "/icons/ip-192.png",
        badge: "/icons/ip-32.png",
        tag: data.tag || "iprojects",
        data: { url: data.url || "/" },
        dir: documentDir()
    };

    function documentDir() { return "auto"; }

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener("notificationclick", function (event) {
    event.notification.close();

    const target = (event.notification.data && event.notification.data.url) || "/";

    event.waitUntil(
        self.clients.matchAll({ type: "window", includeUncontrolled: true }).then(function (clients) {
            for (const client of clients) {
                if (client.url.includes(location.origin) && "focus" in client) {
                    client.navigate(target);
                    return client.focus();
                }
            }
            return self.clients.openWindow(target);
        })
    );
});
