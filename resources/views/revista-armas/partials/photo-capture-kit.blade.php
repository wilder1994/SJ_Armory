@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css">
        <style>
            .sj-image-editor-canvas { max-height: min(42dvh, 380px); }
            .sj-image-editor-canvas .cropper-container,
            .sj-image-editor-canvas .cropper-canvas,
            .sj-image-editor-canvas .cropper-wrap-box,
            .sj-image-editor-canvas img { max-height: min(42dvh, 380px) !important; }
            .revista-slot-card { cursor: pointer; transition: border-color .15s ease, box-shadow .15s ease; }
            .revista-slot-card:hover { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(11, 111, 182, 0.12); }
        </style>
    @endpush
@endonce

<div id="revista_photo_source_modal" class="fixed inset-0 z-[1060] hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-sm rounded-xl bg-white shadow-lg">
        <div class="border-b px-4 py-3 text-sm font-semibold text-gray-800">{{ __('Agregar imagen') }}</div>
        <div class="space-y-2 p-4">
            <button type="button" data-revista-source="camera" class="w-full rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2.5 text-sm font-medium text-indigo-900">{{ __('Tomar foto') }}</button>
            <button type="button" data-revista-source="gallery" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-700">{{ __('Elegir de galería') }}</button>
        </div>
        <div class="flex justify-end border-t px-4 py-2">
            <button type="button" data-revista-source-cancel class="text-sm text-gray-600">{{ __('Cancelar') }}</button>
        </div>
    </div>
</div>

<div id="revista_image_editor_modal" class="fixed inset-0 z-[1070] hidden items-center justify-center overflow-hidden bg-black/50 p-2 sm:p-4">
    <div class="sj-image-editor-panel flex max-h-[calc(100dvh-0.5rem)] w-full max-w-3xl flex-col overflow-hidden rounded-lg bg-white shadow-lg">
        <div class="flex shrink-0 items-center justify-between border-b px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-800">{{ __('Editar imagen') }}</h3>
            <button type="button" data-revista-editor-close class="text-sm text-gray-500">{{ __('Cerrar') }}</button>
        </div>
        <div class="min-h-0 flex-1 overflow-y-auto p-3 sm:p-4">
            <div class="sj-image-editor-canvas w-full overflow-auto rounded bg-gray-50">
                <img id="revista_image_editor_image" alt="" class="mx-auto w-full max-w-full object-contain">
            </div>
        </div>
        <div class="shrink-0 border-t bg-white pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            <div class="flex gap-2 px-3 py-3 sm:px-4">
                <button type="button" data-revista-editor-cancel class="min-h-11 flex-1 rounded-md border border-gray-300 px-3 py-2.5 text-sm font-medium text-gray-700">{{ __('Cancelar') }}</button>
                <button type="button" data-revista-editor-save class="min-h-11 flex-1 rounded-md bg-indigo-600 px-3 py-2.5 text-sm font-semibold text-white">{{ __('Guardar') }}</button>
            </div>
        </div>
    </div>
</div>

<input id="revista_photo_pick_gallery" type="file" accept="image/*" class="hidden">
<input id="revista_photo_pick_camera" type="file" accept="image/*" capture="environment" class="hidden">

@once
    @push('scripts')
        <script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
        <script>
        window.initRevistaPhotoCapture = (config) => {
            const sourceModal = document.getElementById('revista_photo_source_modal');
            const editorModal = document.getElementById('revista_image_editor_modal');
            const editorImage = document.getElementById('revista_image_editor_image');
            const pickGallery = document.getElementById('revista_photo_pick_gallery');
            const pickCamera = document.getElementById('revista_photo_pick_camera');
            const csrfToken = config.csrfToken;
            let storeUrl = '';
            let activeDescription = '';
            let cropper = null;
            let objectUrl = null;

            const openModal = (el) => { el?.classList.remove('hidden'); el?.classList.add('flex'); };
            const closeModal = (el) => { el?.classList.add('hidden'); el?.classList.remove('flex'); };

            const destroyCropper = () => {
                if (cropper) { cropper.destroy(); cropper = null; }
                if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
            };

            const openEditor = (file) => {
                destroyCropper();
                objectUrl = URL.createObjectURL(file);
                editorImage.src = objectUrl;
                openModal(editorModal);
                cropper = new Cropper(editorImage, { viewMode: 1, autoCropArea: 1 });
            };

            const uploadBlob = (blob) => {
                const formData = new FormData();
                formData.append('photo', blob, 'revista.jpg');
                formData.append('description', activeDescription);
                return fetch(storeUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: formData,
                }).then((r) => {
                    if (!r.ok) throw new Error('upload failed');
                    return r.json();
                });
            };

            document.querySelectorAll('[data-revista-source]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const kind = btn.getAttribute('data-revista-source');
                    closeModal(sourceModal);
                    if (kind === 'camera') pickCamera.click();
                    if (kind === 'gallery') pickGallery.click();
                });
            });
            document.querySelector('[data-revista-source-cancel]')?.addEventListener('click', () => closeModal(sourceModal));

            const onPick = (event) => {
                const file = event.target.files?.[0];
                event.target.value = '';
                if (file) openEditor(file);
            };
            pickGallery?.addEventListener('change', onPick);
            pickCamera?.addEventListener('change', onPick);

            document.querySelector('[data-revista-editor-close]')?.addEventListener('click', () => { destroyCropper(); closeModal(editorModal); });
            document.querySelector('[data-revista-editor-cancel]')?.addEventListener('click', () => { destroyCropper(); closeModal(editorModal); });
            document.querySelector('[data-revista-editor-save]')?.addEventListener('click', () => {
                if (!cropper) return;
                cropper.getCroppedCanvas().toBlob((blob) => {
                    if (!blob) return;
                    uploadBlob(blob)
                        .then(() => { destroyCropper(); closeModal(editorModal); config.onSuccess?.(); })
                        .catch(() => alert(@json(__('No se pudo guardar la imagen.'))));
                }, 'image/jpeg', 0.92);
            });

            return {
                openSlot(url, description) {
                    storeUrl = url;
                    activeDescription = description;
                    openModal(sourceModal);
                },
            };
        };
        </script>
    @endpush
@endonce
