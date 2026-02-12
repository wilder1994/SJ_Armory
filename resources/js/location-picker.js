import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';
import municipios from '../data/colombia_municipios.json';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl,
    iconUrl,
    shadowUrl,
});

const buildOption = (value, label) => {
    const option = document.createElement('option');
    option.value = value;
    option.textContent = label;
    return option;
};

const populateDepartments = (select, selected) => {
    const departments = Object.keys(municipios).sort();
    departments.forEach((department) => {
        select.appendChild(buildOption(department, department));
    });
    if (selected) {
        select.value = selected;
    }
};

const populateMunicipalities = (select, department, selected) => {
    select.innerHTML = '';
    select.appendChild(buildOption('', 'Seleccione'));
    if (!department || !municipios[department]) {
        return;
    }
    municipios[department].forEach((municipality) => {
        select.appendChild(buildOption(municipality, municipality));
    });
    if (selected) {
        select.value = selected;
    }
};

const normalizeText = (value) => {
    if (!value) {
        return '';
    }
    return value
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
};

const selectByNormalizedMatch = (select, value) => {
    if (!select || !value) {
        return false;
    }
    const target = normalizeText(value);
    if (!target) {
        return false;
    }
    const options = Array.from(select.options);
    const match = options.find((option) => normalizeText(option.value) === target);
    if (match) {
        select.value = match.value;
        return true;
    }
    const simplified = target
        .replace(/\bmunicipio\b/g, '')
        .replace(/\bciudad\b/g, '')
        .replace(/\bcity\b/g, '')
        .replace(/\s+/g, ' ')
        .trim();
    if (simplified) {
        const partialMatch = options.find((option) => normalizeText(option.value) === simplified);
        if (partialMatch) {
            select.value = partialMatch.value;
            return true;
        }
    }
    const containsMatch = options.find((option) => {
        const candidate = normalizeText(option.value);
        return candidate && (target.includes(candidate) || candidate.includes(target));
    });
    if (containsMatch) {
        select.value = containsMatch.value;
        return true;
    }
    return false;
};

const initLocationSelects = () => {
    const departmentSelects = document.querySelectorAll('[data-department-select]');
    if (!departmentSelects.length) {
        return;
    }

    departmentSelects.forEach((departmentSelect) => {
        const form = departmentSelect.closest('[data-location-form]');
        if (!form) {
            return;
        }
        const municipalitySelect = form.querySelector('[data-municipality-select]');
        if (!municipalitySelect) {
            return;
        }

        const currentDepartment = departmentSelect.dataset.current || '';
        const currentMunicipality = municipalitySelect.dataset.current || '';

        departmentSelect.innerHTML = '';
        departmentSelect.appendChild(buildOption('', 'Seleccione'));
        populateDepartments(departmentSelect, currentDepartment);
        populateMunicipalities(municipalitySelect, currentDepartment, currentMunicipality);

        departmentSelect.addEventListener('change', (event) => {
            populateMunicipalities(municipalitySelect, event.target.value, '');
        });
    });
};

const initMapPicker = () => {
    const triggers = document.querySelectorAll('[data-map-trigger]');
    if (!triggers.length) {
        return;
    }

    const modal = document.getElementById('location-map-modal');
    const closeButtons = modal ? modal.querySelectorAll('[data-map-close]') : [];
    const mapElement = modal ? modal.querySelector('#location-map') : null;
    const acceptButton = modal ? modal.querySelector('[data-map-accept]') : null;
    const errorMessage = modal ? modal.querySelector('[data-map-error]') : null;
    if (!modal || !mapElement) {
        return;
    }

    let mapInstance = null;
    let marker = null;
    let selectedLatLng = null;
    let activeForm = null;

    const resolveInputs = () => {
        if (!activeForm) {
            return {};
        }
        return {
            latInput: activeForm.querySelector('[data-latitude-input]'),
            lngInput: activeForm.querySelector('[data-longitude-input]'),
            addressInput: activeForm.querySelector('[data-address-input]'),
            departmentSelect: activeForm.querySelector('[data-department-select]'),
            municipalitySelect: activeForm.querySelector('[data-municipality-select]'),
            coordsSourceInput: activeForm.querySelector('[data-coords-source]'),
        };
    };

    const openModal = (form) => {
        activeForm = form;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        selectedLatLng = null;
        if (acceptButton) {
            acceptButton.disabled = true;
        }
        if (errorMessage) {
            errorMessage.textContent = '';
            errorMessage.classList.add('hidden');
        }

        const { latInput, lngInput, coordsSourceInput } = resolveInputs();
        if (coordsSourceInput) {
            coordsSourceInput.value = 'geocode';
        }

        if (!mapInstance) {
            mapInstance = L.map(mapElement).setView([4.5709, -74.2973], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(mapInstance);

            mapInstance.on('click', (event) => {
                const { lat, lng } = event.latlng;
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng]).addTo(mapInstance);
                }
                selectedLatLng = { lat, lng };
                const { latInput: clickLat, lngInput: clickLng, coordsSourceInput: clickSource } = resolveInputs();
                if (clickLat) {
                    clickLat.value = lat.toFixed(6);
                }
                if (clickLng) {
                    clickLng.value = lng.toFixed(6);
                }
                if (clickSource) {
                    clickSource.value = 'map';
                }
                if (acceptButton) {
                    acceptButton.disabled = false;
                }
            });
        }

        if (latInput?.value && lngInput?.value) {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng]).addTo(mapInstance);
                }
                selectedLatLng = { lat, lng };
                if (acceptButton) {
                    acceptButton.disabled = false;
                }
                mapInstance.setView([lat, lng], 14);
            }
        }

        setTimeout(() => {
            mapInstance.invalidateSize();
        }, 200);
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    const reverseGeocode = async (lat, lng) => {
        const backendUrl = new URL('/geocode/reverse', window.location.origin);
        backendUrl.searchParams.set('lat', lat);
        backendUrl.searchParams.set('lng', lng);
        try {
            const response = await fetch(backendUrl.toString(), {
                headers: {
                    'Accept': 'application/json',
                },
            });
            if (response.ok) {
                return response.json();
            }
        } catch (error) {
            // fallback to client-side below
        }

        const publicUrl = new URL('https://nominatim.openstreetmap.org/reverse');
        publicUrl.searchParams.set('format', 'jsonv2');
        publicUrl.searchParams.set('lat', lat);
        publicUrl.searchParams.set('lon', lng);
        publicUrl.searchParams.set('addressdetails', '1');
        const response = await fetch(publicUrl.toString(), {
            headers: {
                'Accept': 'application/json',
                'Accept-Language': 'es',
            },
        });
        if (!response.ok) {
            throw new Error('reverse geocode failed');
        }
        return response.json();
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            openModal(trigger.closest('[data-location-form]'));
        });
    });

    document.addEventListener('change', (event) => {
        if (!activeForm) {
            return;
        }
        if (!activeForm.contains(event.target)) {
            return;
        }
        const field = event.target;
        const isAddress = field.matches('[data-address-input]');
        const isDepartment = field.matches('[data-department-select]');
        const isMunicipality = field.matches('[data-municipality-select]');
        if (!isAddress && !isDepartment && !isMunicipality) {
            return;
        }
        const { latInput, lngInput, coordsSourceInput } = resolveInputs();
        if (coordsSourceInput) {
            coordsSourceInput.value = 'geocode';
        }
        if (latInput) {
            latInput.value = '';
        }
        if (lngInput) {
            lngInput.value = '';
        }
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            closeModal();
        });
    });

    if (acceptButton) {
        acceptButton.addEventListener('click', async (event) => {
            event.preventDefault();
            if (!selectedLatLng) {
                return;
            }
            const { addressInput, departmentSelect, municipalitySelect } = resolveInputs();
            const originalText = acceptButton.textContent;
            acceptButton.textContent = 'Buscando...';
            acceptButton.disabled = true;
            if (errorMessage) {
                errorMessage.textContent = '';
                errorMessage.classList.add('hidden');
            }
            try {
                const data = await reverseGeocode(selectedLatLng.lat, selectedLatLng.lng);
                const address = data?.address ?? {};
                const road = address.road || address.pedestrian || address.path || address.cycleway || '';
                const number = address.house_number ? ` ${address.house_number}` : '';
                const neighborhood = address.neighbourhood || address.suburb || '';
                const addressValue = road ? `${road}${number}` : (data?.display_name ?? '');

                const municipality =
                    address.city ||
                    address.town ||
                    address.village ||
                    address.municipality ||
                    address.county ||
                    '';
                const department = address.state || '';

                if (addressInput && addressValue) {
                    addressInput.value = addressValue;
                }

                if (departmentSelect && department) {
                    if (!selectByNormalizedMatch(departmentSelect, department)) {
                        const option = buildOption(department, department);
                        departmentSelect.appendChild(option);
                        departmentSelect.value = department;
                    }
                    const departmentValue = departmentSelect.value || department;
                    populateMunicipalities(municipalitySelect, departmentValue, '');
                }

                if (municipalitySelect && municipality) {
                    if (!selectByNormalizedMatch(municipalitySelect, municipality)) {
                        const option = buildOption(municipality, municipality);
                        municipalitySelect.appendChild(option);
                        municipalitySelect.value = municipality;
                    }
                }
                if (coordsSourceInput) {
                    coordsSourceInput.value = 'map';
                }
                if (!addressValue || !department || !municipality) {
                    if (errorMessage) {
                        errorMessage.textContent = 'No se pudo completar la direcciÃ³n o el municipio. Ajusta manualmente si es necesario.';
                        errorMessage.classList.remove('hidden');
                    }
                } else {
                    closeModal();
                }
            } catch (error) {
                if (errorMessage) {
                    errorMessage.textContent = 'No se pudo obtener la ubicaciÃ³n. Intenta nuevamente o completa los datos manualmente.';
                    errorMessage.classList.remove('hidden');
                }
            } finally {
                acceptButton.textContent = originalText;
                acceptButton.disabled = false;
                closeModal();
            }
        });
    }

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
};

const init = () => {
    initLocationSelects();
    initMapPicker();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

