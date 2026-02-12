@php
    $weapon = $weapon ?? null;
    $photoDescriptions = \App\Models\WeaponPhoto::DESCRIPTIONS;
    $existingPhotos = $weapon?->photos?->keyBy('description') ?? collect();
    $photoIndex = 1;
@endphp

@if (!empty($showInternalCode))
    <div>
        <x-input-label for="internal_code" :value="__('Código interno')" />
        <x-text-input id="internal_code" name="internal_code" type="text" class="mt-1 block w-full" value="{{ old('internal_code', $weapon?->internal_code) }}" required />
        <x-input-error :messages="$errors->get('internal_code')" class="mt-2" />
    </div>
@endif

<div>
    <x-input-label for="serial_number" :value="__('Número de serie')" />
    <x-text-input id="serial_number" name="serial_number" type="text" class="mt-1 block w-full" value="{{ old('serial_number', $weapon?->serial_number) }}" required />
    <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
</div>

<div>
    <x-input-label for="weapon_type" :value="__('Tipo de arma')" />
    <x-text-input id="weapon_type" name="weapon_type" type="text" class="mt-1 block w-full" value="{{ old('weapon_type', $weapon?->weapon_type) }}" required />
    <x-input-error :messages="$errors->get('weapon_type')" class="mt-2" />
</div>

<div>
    <x-input-label for="caliber" :value="__('Calibre')" />
    <x-text-input id="caliber" name="caliber" type="text" class="mt-1 block w-full" value="{{ old('caliber', $weapon?->caliber) }}" required />
    <x-input-error :messages="$errors->get('caliber')" class="mt-2" />
</div>

<div>
    <x-input-label for="brand" :value="__('Marca')" />
    <x-text-input id="brand" name="brand" type="text" class="mt-1 block w-full" value="{{ old('brand', $weapon?->brand) }}" required />
    <x-input-error :messages="$errors->get('brand')" class="mt-2" />
</div>

<div>
    <x-input-label for="capacity" :value="__('Capacidad')" />
    <x-text-input id="capacity" name="capacity" type="text" class="mt-1 block w-full" value="{{ old('capacity', $weapon?->capacity) }}" required />
    <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
</div>

<div>
    <x-input-label for="ownership_type" :value="__('Tipo de propiedad')" />
    <select id="ownership_type" name="ownership_type" class="mt-1 block w-full rounded-md border-gray-300">
        @foreach ($ownershipTypes as $value => $label)
            <option value="{{ $value }}" @selected(old('ownership_type', $weapon?->ownership_type) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('ownership_type')" class="mt-2" />
</div>

<div>
    <x-input-label for="ownership_entity" :value="__('Entidad de propiedad (si aplica)')" />
    <x-text-input id="ownership_entity" name="ownership_entity" type="text" class="mt-1 block w-full" value="{{ old('ownership_entity', $weapon?->ownership_entity) }}" />
    <x-input-error :messages="$errors->get('ownership_entity')" class="mt-2" />
</div>

<div class="md:col-span-2">
    <span class="text-sm font-medium text-gray-700">{{ __('Fotos del arma') }}</span>
    <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-5">
        @foreach ($photoDescriptions as $description => $label)
            @php
                $photo = $existingPhotos->get($description);
                $photoUrl = $photo?->file
                    ? Storage::disk($photo->file->disk)->url($photo->file->path)
                    : null;
            @endphp
            <label for="photo_{{ $photoIndex }}" class="block cursor-pointer">
                <input id="photo_{{ $photoIndex }}" name="photos[]" type="file" accept="image/*" class="hidden"
                    data-preview-target="photo_preview_{{ $photoIndex }}" data-placeholder-target="photo_placeholder_{{ $photoIndex }}" />
                <div class="flex h-24 w-full items-center justify-center rounded border border-dashed border-gray-300 bg-gray-50 text-xs text-gray-500">
                    <span id="photo_placeholder_{{ $photoIndex }}" @class(['hidden' => $photoUrl])>
                        {{ __('Seleccionar foto') }}
                    </span>
                    <img id="photo_preview_{{ $photoIndex }}" alt="Previsualizacion"
                        class="{{ $photoUrl ? '' : 'hidden' }} h-full w-full rounded object-cover"
                        @if ($photoUrl) src="{{ $photoUrl }}" @endif />
                </div>
                <div class="mt-1 text-xs text-gray-500">{{ $label }}</div>
            </label>
            @php $photoIndex++; @endphp
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('photos')" class="mt-2" />
    @php
        $photoErrors = collect($errors->get('photos.*'))->flatten()->all();
    @endphp
    <x-input-error :messages="$photoErrors" class="mt-2" />
</div>

<div class="md:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <x-input-label for="permit_type" :value="__('Tipo de permiso')" />
    <select id="permit_type" name="permit_type" class="mt-1 block w-full rounded-md border-gray-300" required>
        <option value="">{{ __('Seleccione') }}</option>
        <option value="porte" @selected(old('permit_type', $weapon?->permit_type) === 'porte')>{{ __('Porte') }}</option>
        <option value="tenencia" @selected(old('permit_type', $weapon?->permit_type) === 'tenencia')>{{ __('Tenencia') }}</option>
    </select>
    <x-input-error :messages="$errors->get('permit_type')" class="mt-2" />
    @if ($errors->has('permit_type'))
        <div class="mt-1 text-xs text-red-600">{{ __('Seleccione Porte o Tenencia.') }}</div>
    @endif
</div>

    <div>
        <x-input-label for="permit_number" :value="__('Número de permiso')" />
        <x-text-input id="permit_number" name="permit_number" type="text" class="mt-1 block w-full" value="{{ old('permit_number', $weapon?->permit_number) }}" />
        <x-input-error :messages="$errors->get('permit_number')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="permit_expires_at" :value="__('Fecha de vencimiento')" />
        <x-text-input id="permit_expires_at" name="permit_expires_at" type="date" class="mt-1 block w-full" value="{{ old('permit_expires_at', optional($weapon?->permit_expires_at)->format('Y-m-d')) }}" />
        <x-input-error :messages="$errors->get('permit_expires_at')" class="mt-2" />
    </div>

    <div class="md:col-start-2 md:row-start-1 md:row-span-3 flex flex-col">
        <x-input-label for="permit_photo" :value="__('Foto del permiso')" />
        <label for="permit_photo" class="mt-1 block h-full cursor-pointer">
            <input id="permit_photo" name="permit_photo" type="file" accept="image/*" class="hidden" @if (!empty($requirePermitPhoto)) required @endif
                data-preview-target="permit_preview" data-placeholder-target="permit_placeholder" />
            <div class="flex h-full min-h-[12rem] w-full items-center justify-center rounded border border-dashed border-gray-300 bg-gray-50 text-xs text-gray-500">
                <span id="permit_placeholder">{{ __('Seleccionar foto') }}</span>
                <img id="permit_preview" alt="Previsualizacion"
                    class="hidden h-full w-full rounded object-cover" />
            </div>
        </label>
        <x-input-error :messages="$errors->get('permit_photo')" class="mt-2" />
        @if ($errors->has('permit_photo'))
            <div class="mt-1 text-xs text-red-600">{{ __('Debe agregar la foto del permiso.') }}</div>
        @endif
        <div id="permit_photo_alert" class="mt-1 text-xs text-red-600 hidden">
            {{ __('Debe agregar la foto del permiso.') }}
        </div>
    </div>
</div>

<div class="md:col-span-2">
    <x-input-label for="notes" :value="__('Notas')" />
    <textarea id="notes" name="notes" spellcheck="true" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $weapon?->notes) }}</textarea>
    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
</div>

<div class="md:col-span-2 flex justify-end gap-2">
    <a href="{{ $cancelUrl }}" class="text-sm text-gray-600 hover:text-gray-900">
        {{ __('Cancelar') }}
    </a>
    <x-primary-button>
        {{ $submitLabel }}
    </x-primary-button>
</div>

<div id="image_editor_modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-3xl rounded bg-white shadow-lg">
        <div class="flex items-center justify-between border-b px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-800">{{ __('Editar imagen') }}</h3>
            <button id="image_editor_close" type="button" class="text-sm text-gray-500 hover:text-gray-700">
                {{ __('Cerrar') }}
            </button>
        </div>
        <div class="p-4">
            <div class="max-h-[70vh] w-full overflow-hidden">
                <img id="image_editor_image" alt="Editor" class="max-h-[70vh] w-full object-contain" />
            </div>
        </div>
        <div class="flex items-center justify-between gap-2 border-t px-4 py-3">
            <div class="flex items-center gap-2">
                <button id="image_editor_rotate_left" type="button" class="rounded border border-gray-300 px-3 py-1 text-xs text-gray-700 hover:bg-gray-100">
                    {{ __('Girar izquierda') }}
                </button>
                <button id="image_editor_rotate_right" type="button" class="rounded border border-gray-300 px-3 py-1 text-xs text-gray-700 hover:bg-gray-100">
                    {{ __('Girar derecha') }}
                </button>
            </div>
            <button id="image_editor_cancel" type="button" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Cancelar') }}
            </button>
            <button id="image_editor_crop" type="button" class="rounded bg-indigo-600 px-3 py-1 text-xs text-white hover:bg-indigo-700">
                {{ __('Guardar') }}
            </button>
        </div>
    </div>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css">
    @endpush
@endonce
@push('scripts')
    <script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
        let activeInput = null;
        let cropper = null;

    const modal = document.getElementById('image_editor_modal');
    const modalImage = document.getElementById('image_editor_image');
    const closeButton = document.getElementById('image_editor_close');
    const cancelButton = document.getElementById('image_editor_cancel');
    const cropButton = document.getElementById('image_editor_crop');
    const rotateLeftButton = document.getElementById('image_editor_rotate_left');
    const rotateRightButton = document.getElementById('image_editor_rotate_right');

    const setPreview = (preview, placeholder, file) => {
        if (!preview || !placeholder || !file) {
            return;
        }

        if (preview.dataset.objectUrl) {
            URL.revokeObjectURL(preview.dataset.objectUrl);
        }

        const url = URL.createObjectURL(file);
        preview.dataset.objectUrl = url;
        preview.src = url;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
    };

    const clearPreview = (preview, placeholder) => {
        if (!preview || !placeholder) {
            return;
        }

        if (preview.dataset.objectUrl) {
            URL.revokeObjectURL(preview.dataset.objectUrl);
            delete preview.dataset.objectUrl;
        }

        preview.removeAttribute('src');
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
    };

    const openEditor = (input) => {
        const file = input.files && input.files[0];
        if (!file) {
            return;
        }

        activeInput = input;
        if (modalImage.dataset.objectUrl) {
            URL.revokeObjectURL(modalImage.dataset.objectUrl);
        }
        modalImage.dataset.objectUrl = URL.createObjectURL(file);
        modalImage.src = modalImage.dataset.objectUrl;
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (cropper) {
            cropper.destroy();
        }

        cropper = new Cropper(modalImage, {
            viewMode: 1,
            autoCropArea: 1,
        });
    };

    const closeEditor = (discardSelection = false) => {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (modalImage.dataset.objectUrl) {
            URL.revokeObjectURL(modalImage.dataset.objectUrl);
            delete modalImage.dataset.objectUrl;
        }
        modalImage.removeAttribute('src');

        if (discardSelection && activeInput) {
            const previewId = activeInput.dataset.previewTarget;
            const placeholderId = activeInput.dataset.placeholderTarget;
            const preview = previewId ? document.getElementById(previewId) : null;
            const placeholder = placeholderId ? document.getElementById(placeholderId) : null;
            clearPreview(preview, placeholder);
            activeInput.value = '';
        }

        activeInput = null;
    };

    const applyCrop = () => {
        if (!cropper || !activeInput) {
            closeEditor();
            return;
        }

        cropper.getCroppedCanvas().toBlob((blob) => {
            if (!blob) {
                closeEditor();
                return;
            }

            const fileName = activeInput.files[0]?.name || 'photo.jpg';
            const file = new File([blob], fileName, { type: blob.type });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            activeInput.files = dataTransfer.files;

            const previewId = activeInput.dataset.previewTarget;
            const placeholderId = activeInput.dataset.placeholderTarget;
            const preview = previewId ? document.getElementById(previewId) : null;
            const placeholder = placeholderId ? document.getElementById(placeholderId) : null;
            setPreview(preview, placeholder, file);

            closeEditor();
        }, 'image/jpeg', 0.92);
    };

    closeButton.addEventListener('click', () => closeEditor(true));
    cancelButton.addEventListener('click', () => closeEditor(true));
    cropButton.addEventListener('click', applyCrop);
    rotateLeftButton.addEventListener('click', () => cropper && cropper.rotate(-90));
    rotateRightButton.addEventListener('click', () => cropper && cropper.rotate(90));

        document.querySelectorAll('input[data-preview-target]').forEach((input) => {
            input.addEventListener('change', () => openEditor(input));
        });

        const form = document.querySelector('form[enctype="multipart/form-data"]');
        const permitInput = document.getElementById('permit_photo');
        const permitAlert = document.getElementById('permit_photo_alert');

        if (form && permitInput && permitAlert) {
            form.addEventListener('submit', (event) => {
                const isCreate = @json(!empty($requirePermitPhoto));
                if (!isCreate) {
                    return;
                }

                if (!permitInput.files || permitInput.files.length === 0) {
                    event.preventDefault();
                    permitAlert.classList.remove('hidden');
                    permitInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    permitAlert.classList.add('hidden');
                }
            });
        }
    </script>
@endpush




