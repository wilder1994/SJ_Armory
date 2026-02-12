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

    const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    });
    const satelliteLayer = L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        {
            maxZoom: 19,
            attribution: 'Tiles &copy; Esri',
        }
    );
    const labelsLayer = L.tileLayer(
        'https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
        {
            maxZoom: 19,
            attribution: 'Labels &copy; Esri',
        }
    );
    const baseLayers = {
        Mapa: streetLayer,
        Satelite: satelliteLayer,
        Hibrida: L.layerGroup([satelliteLayer, labelsLayer]),
    };
    baseLayers.Hibrida.addTo(map);
    L.control.layers(baseLayers, null, { position: 'topright' }).addTo(map);

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
                        iconSize: [30, 44],
                        iconAnchor: [15, 44],
                        popupAnchor: [1, -38],
                    });
                },
            });
            const bounds = [];

            grouped.forEach((groupItems) => {
                const { lat, lng } = groupItems[0];
                const clientName = groupItems[0].client ?? '-';
                const rows = groupItems
                    .map(
                        (item) => `
                    <tr>
                        <td class="pr-3 py-1">${item.serial_number ?? '-'}</td>
                        <td class="py-1 text-right">
                            <a href="${item.link}" target="_blank">Ver arma</a>
                        </td>
                    </tr>
                `
                    )
                    .join('');
                const popup = `
                    <div class="text-sm">
                        <div class="font-semibold mb-1">${clientName}</div>
                        <div class="mb-2 text-xs text-gray-600">Cantidad de armas: ${groupItems.length}</div>
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-left text-gray-600">
                                    <th class="pr-3">Serie</th>
                                    <th class="text-right">Detalle</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
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

