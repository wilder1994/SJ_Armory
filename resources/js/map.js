import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
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

            const bounds = [];
            items.forEach((item) => {
                const { lat, lng } = item;
                const marker = L.marker([lat, lng]).addTo(map);
                const popup = `
                    <div class="text-sm">
                        <div class="font-medium">${item.internal_code ?? '-'}</div>
                        <div>${item.client ?? '-'}</div>
                        <div>${item.responsible ?? '-'}</div>
                        <div>${item.location ?? '-'}</div>
                        <a href="${item.link}" target="_blank">Ver arma</a>
                    </div>
                `;
                marker.bindPopup(popup);
                bounds.push([lat, lng]);
            });

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
