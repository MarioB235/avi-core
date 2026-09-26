import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const MAP_SELECTOR = '[data-avicore-client-map]';
const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)';

const markerStyle = {
    radius: 7,
    fillColor: '#1f5e3b',
    color: '#ffffff',
    weight: 2,
    opacity: 0.95,
    fillOpacity: 0.92,
};

const markerStyleActive = {
    ...markerStyle,
    radius: 9,
    fillColor: '#2d7a4f',
    weight: 2.5,
    fillOpacity: 1,
};

function prefersReducedMotion() {
    return window.matchMedia?.(REDUCED_MOTION_QUERY)?.matches ?? false;
}

function parseClients(root) {
    try {
        return JSON.parse(root.dataset.clients ?? '[]');
    } catch {
        return [];
    }
}

function isVisible(element) {
    if (!element) {
        return false;
    }

    const rect = element.getBoundingClientRect();

    return rect.width > 0 && rect.height > 0;
}

function refreshMapSize(map) {
    if (!map) {
        return;
    }

    requestAnimationFrame(() => {
        map.invalidateSize({ pan: false });
    });
}

function destroyMap(root) {
    if (root._avicoreClientMapResizeObserver) {
        root._avicoreClientMapResizeObserver.disconnect();
        delete root._avicoreClientMapResizeObserver;
    }

    if (root._avicoreClientMap) {
        root._avicoreClientMap.remove();
        delete root._avicoreClientMap;
    }

    delete root._avicoreClientMapMarkers;
    delete root.dataset.mapReady;
}

function highlightMarker(markers, id) {
    markers.forEach((marker, markerId) => {
        marker.setStyle(markerId === id ? markerStyleActive : markerStyle);
    });
}

function focusClientOnMap(root, map, markers, clients, id) {
    const client = clients.find((item) => item.id === id);
    const marker = markers.get(id);

    if (!client || !marker) {
        return;
    }

    highlightMarker(markers, id);

    const zoom = Math.max(map.getZoom(), 12);

    map.setView([client.lat, client.lng], zoom, {
        animate: !prefersReducedMotion(),
    });
}

function mountMap(root) {
    if (!root || root.dataset.mapReady === '1') {
        return;
    }

    const canvas = root.querySelector('[data-avicore-client-map-canvas]');

    if (!canvas || !isVisible(canvas)) {
        return;
    }

    const clients = parseClients(root);

    if (clients.length === 0) {
        return;
    }

    const map = L.map(canvas, {
        scrollWheelZoom: false,
        zoomControl: false,
        attributionControl: false,
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 19,
    }).addTo(map);

    L.control.attribution({
        position: 'bottomleft',
        prefix: false,
    }).addTo(map);

    L.control.zoom({
        position: 'bottomright',
    }).addTo(map);

    const bounds = [];
    const markers = new Map();

    clients.forEach((client) => {
        const marker = L.circleMarker([client.lat, client.lng], markerStyle).addTo(map);

        marker.on('click', () => {
            focusClientOnMap(root, map, markers, clients, client.id);

            root.dispatchEvent(new CustomEvent('avicore-client-map-select', {
                bubbles: true,
                detail: { id: client.id },
            }));
        });

        markers.set(client.id, marker);
        bounds.push([client.lat, client.lng]);
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [28, 28], maxZoom: 12 });
    }

    root._avicoreClientMap = map;
    root._avicoreClientMapMarkers = markers;
    root._avicoreClientMapClients = clients;
    root.dataset.mapReady = '1';

    const resizeObserver = new ResizeObserver(() => refreshMapSize(map));
    resizeObserver.observe(canvas);
    root._avicoreClientMapResizeObserver = resizeObserver;

    refreshMapSize(map);
    setTimeout(() => refreshMapSize(map), 250);
    setTimeout(() => refreshMapSize(map), 500);
}

function scheduleMount(root) {
    if (!root || root.dataset.mapReady === '1') {
        return;
    }

    if (isVisible(root)) {
        mountMap(root);

        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            observer.disconnect();
            requestAnimationFrame(() => mountMap(root));
        });
    }, {
        root: null,
        threshold: 0.05,
        rootMargin: '0px 0px -4% 0px',
    });

    observer.observe(root);

    setTimeout(() => {
        observer.disconnect();
        mountMap(root);
    }, 1500);
}

function scan(root = document) {
    const scope = root instanceof Element ? root : document;
    const maps = scope.matches?.(MAP_SELECTOR)
        ? [scope, ...scope.querySelectorAll(MAP_SELECTOR)]
        : [...scope.querySelectorAll(MAP_SELECTOR)];

    maps.forEach((mapRoot) => scheduleMount(mapRoot));
}

function initClientMaps() {
    scan();

    document.addEventListener('avicore-reveal-visible', (event) => {
        const mapRoot = event.target?.querySelector?.(MAP_SELECTOR)
            ?? event.target?.closest?.(MAP_SELECTOR);

        if (mapRoot) {
            scheduleMount(mapRoot);
            refreshMapSize(mapRoot._avicoreClientMap);
        }
    });

    document.addEventListener('livewire:navigated', () => {
        requestAnimationFrame(() => scan());
        setTimeout(() => scan(), 300);
    });

    document.addEventListener('livewire:navigating', () => {
        document.querySelectorAll(MAP_SELECTOR).forEach(destroyMap);
    });

    document.addEventListener('livewire:init', () => {
        if (!window.Livewire?.hook) {
            return;
        }

        Livewire.hook('morph.updated', ({ el }) => {
            if (el?.matches?.(MAP_SELECTOR) || el?.querySelector?.(MAP_SELECTOR)) {
                requestAnimationFrame(() => scan(el));
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initClientMaps);
} else {
    initClientMaps();
}

export function rescanAvicoreClientMaps(root = document) {
    scan(root);
}
