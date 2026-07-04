/**
 * Pickers de fotos embebidos en el formulario de crear chaleco (arrastrar, pegar, seleccionar).
 *
 * @param {HTMLElement} root Element with [data-vest-form-photos]
 */
export function initVestFormPhotos(root) {
    if (!root || root.dataset.vestFormPhotosReady === '1') {
        return;
    }

    root.dataset.vestFormPhotosReady = '1';

    const imagesOnlyMessage = root.dataset.imagesOnlyMessage || 'Solo puede usar archivos de imagen.';
    const dropZones = Array.from(root.querySelectorAll('[data-drop-zone]'));
    let hoveredPasteZone = null;

    const getClipboardImage = (clipboardData) => {
        const items = Array.from(clipboardData?.items || []);
        const imageItem = items.find((item) => item.kind === 'file' && item.type.startsWith('image/'));

        return imageItem ? imageItem.getAsFile() : null;
    };

    const setDropZoneActive = (zone, active) => {
        const surface = zone?.querySelector('[data-drop-surface]');
        if (!surface) {
            return;
        }

        surface.classList.toggle('border-indigo-400', active);
        surface.classList.toggle('bg-indigo-50', active);
        surface.classList.toggle('ring-2', active);
        surface.classList.toggle('ring-indigo-200', active);
    };

    const assignFileToInput = (input, file) => {
        if (!input || !file || !file.type.startsWith('image/')) {
            return false;
        }

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;

        return true;
    };

    const setPreview = (input, file) => {
        const previewId = input.dataset.previewTarget;
        const placeholderId = input.dataset.placeholderTarget;
        const preview = previewId ? document.getElementById(previewId) : null;
        const placeholder = placeholderId ? document.getElementById(placeholderId) : null;

        if (!preview) {
            return;
        }

        if (preview.dataset.objectUrl) {
            URL.revokeObjectURL(preview.dataset.objectUrl);
            delete preview.dataset.objectUrl;
        }

        const objectUrl = URL.createObjectURL(file);
        preview.dataset.objectUrl = objectUrl;
        preview.src = objectUrl;
        preview.classList.remove('hidden');
        placeholder?.classList.add('hidden');
    };

    const handleImageSelection = (input, file) => {
        if (!input || !file) {
            return false;
        }

        if (!file.type.startsWith('image/')) {
            window.alert(imagesOnlyMessage);
            return false;
        }

        if (assignFileToInput(input, file)) {
            setPreview(input, file);
            return true;
        }

        return false;
    };

    dropZones.forEach((zone) => {
        const input = zone.querySelector('input[type="file"]');
        const pasteProxy = zone.querySelector('[data-paste-proxy]');

        if (!input) {
            return;
        }

        ['dragenter', 'dragover'].forEach((eventName) => {
            zone.addEventListener(eventName, (event) => {
                event.preventDefault();
                event.stopPropagation();
                hoveredPasteZone = zone;
                setDropZoneActive(zone, true);
            });
        });

        ['dragleave', 'dragend'].forEach((eventName) => {
            zone.addEventListener(eventName, (event) => {
                event.preventDefault();
                event.stopPropagation();
                if (event.target === zone || !zone.contains(event.relatedTarget)) {
                    setDropZoneActive(zone, false);
                }
            });
        });

        zone.addEventListener('drop', (event) => {
            event.preventDefault();
            event.stopPropagation();
            hoveredPasteZone = zone;
            setDropZoneActive(zone, false);

            const file = event.dataTransfer?.files?.[0];
            if (file) {
                handleImageSelection(input, file);
            }
        });

        zone.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                input.click();
            }
        });

        zone.addEventListener('click', (event) => {
            if (event.target.closest('button, a, input')) {
                return;
            }

            event.preventDefault();
            input.click();
        });

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (file) {
                handleImageSelection(input, file);
            }
        });

        if (pasteProxy) {
            zone.addEventListener('mouseenter', () => {
                hoveredPasteZone = zone;
            });

            zone.addEventListener('mouseleave', () => {
                if (hoveredPasteZone === zone) {
                    hoveredPasteZone = null;
                }
            });

            pasteProxy.addEventListener('mousedown', (event) => {
                if (event.button === 0) {
                    event.preventDefault();
                    pasteProxy.focus({ preventScroll: true });
                }
            });

            pasteProxy.addEventListener('focus', () => {
                hoveredPasteZone = zone;
                pasteProxy.textContent = '';
            });

            pasteProxy.addEventListener('contextmenu', () => {
                hoveredPasteZone = zone;
                pasteProxy.focus({ preventScroll: true });
            });

            pasteProxy.addEventListener('paste', (event) => {
                const file = getClipboardImage(event.clipboardData);
                if (!file) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                handleImageSelection(input, file);
                pasteProxy.textContent = '';
            });

            pasteProxy.addEventListener('blur', () => {
                pasteProxy.textContent = '';
            });
        }
    });

    document.addEventListener('paste', (event) => {
        const file = getClipboardImage(event.clipboardData);
        if (!file) {
            return;
        }

        const zone = hoveredPasteZone;
        const input = zone?.querySelector('input[type="file"]');

        if (!zone || !input || !root.contains(zone)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        handleImageSelection(input, file);
    });
}
