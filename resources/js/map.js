import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import 'leaflet.markercluster';
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl,
    iconUrl,
    shadowUrl,
});

const initMap = () => {
    const mapElement = document.getElementById('weapons-map');
    if (!mapElement) {
        return;
    }

    const endpoint = mapElement.dataset.endpoint;
    const map = L.map(mapElement).setView([4.5709, -74.2973], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    if (!endpoint) {
        return;
    }

    fetch(endpoint)
        .then((response) => response.json())
        .then((items) => {
            if (!Array.isArray(items) || items.length === 0) {
                return;
            }

            const grouped = new Map();
            items.forEach((item) => {
                const key = `${item.lat},${item.lng}`;
                if (!grouped.has(key)) {
                    grouped.set(key, []);
                }
                grouped.get(key).push(item);
            });

            const clusterGroup = L.markerClusterGroup({
                iconCreateFunction: (cluster) => {
                    const count = cluster.getChildCount();
                    return L.divIcon({
                        html: `
                            <div class="sj-cluster-icon">
                                <img src="${iconUrl}" alt="" />
                                <span>${count}</span>
                            </div>
                        `,
                        className: 'sj-cluster-wrapper',
                        iconSize: [26, 41],
                        iconAnchor: [13, 41],
                        popupAnchor: [1, -34],
                    });
                },
            });
            const bounds = [];

            grouped.forEach((groupItems) => {
                const { lat, lng } = groupItems[0];
                const list = groupItems.map((item) => `
                    <li class="mb-2">
                        <div class="font-medium">${item.internal_code ?? '-'}</div>
                        <div>${item.client ?? '-'}</div>
                        <div>${item.responsible ?? '-'}</div>
                        <div>${item.location ?? '-'}</div>
                        <a href="${item.link}" target="_blank">Ver arma</a>
                    </li>
                `).join('');
                const popup = `
                    <div class="text-sm">
                        <div class="font-semibold mb-2">Armas en este punto: ${groupItems.length}</div>
                        <ul class="list-disc pl-4">${list}</ul>
                    </div>
                `;

                const marker = L.marker([lat, lng]);
                marker.bindPopup(popup);
                clusterGroup.addLayer(marker);
                bounds.push([lat, lng]);
            });

            map.addLayer(clusterGroup);

            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [30, 30] });
            }
        })
        .catch(() => {});
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMap);
} else {
    initMap();
}
