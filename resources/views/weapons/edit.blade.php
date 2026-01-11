<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar arma') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('weapons.update', $weapon) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="internal_code" :value="__('Código interno')" />
                            <x-text-input id="internal_code" name="internal_code" type="text" class="mt-1 block w-full" value="{{ old('internal_code', $weapon->internal_code) }}" required />
                            <x-input-error :messages="$errors->get('internal_code')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="serial_number" :value="__('Número de serie')" />
                            <x-text-input id="serial_number" name="serial_number" type="text" class="mt-1 block w-full" value="{{ old('serial_number', $weapon->serial_number) }}" required />
                            <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="weapon_type" :value="__('Tipo de arma')" />
                            <x-text-input id="weapon_type" name="weapon_type" type="text" class="mt-1 block w-full" value="{{ old('weapon_type', $weapon->weapon_type) }}" required />
                            <x-input-error :messages="$errors->get('weapon_type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="caliber" :value="__('Calibre')" />
                            <x-text-input id="caliber" name="caliber" type="text" class="mt-1 block w-full" value="{{ old('caliber', $weapon->caliber) }}" required />
                            <x-input-error :messages="$errors->get('caliber')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="brand" :value="__('Marca')" />
                            <x-text-input id="brand" name="brand" type="text" class="mt-1 block w-full" value="{{ old('brand', $weapon->brand) }}" required />
                            <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="model" :value="__('Modelo')" />
                            <x-text-input id="model" name="model" type="text" class="mt-1 block w-full" value="{{ old('model', $weapon->model) }}" required />
                            <x-input-error :messages="$errors->get('model')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="operational_status" :value="__('Estado operativo')" />
                            <select id="operational_status" name="operational_status" class="mt-1 block w-full rounded-md border-gray-300">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('operational_status', $weapon->operational_status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('operational_status')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="ownership_type" :value="__('Tipo de propiedad')" />
                            <select id="ownership_type" name="ownership_type" class="mt-1 block w-full rounded-md border-gray-300">
                                @foreach ($ownershipTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('ownership_type', $weapon->ownership_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('ownership_type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="ownership_entity" :value="__('Entidad de propiedad (si aplica)')" />
                            <x-text-input id="ownership_entity" name="ownership_entity" type="text" class="mt-1 block w-full" value="{{ old('ownership_entity', $weapon->ownership_entity) }}" />
                            <x-input-error :messages="$errors->get('ownership_entity')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <x-input-label for="permit_type" :value="__('Tipo de permiso')" />
                                <x-text-input id="permit_type" name="permit_type" type="text" class="mt-1 block w-full" value="{{ old('permit_type', $weapon->permit_type) }}" />
                                <x-input-error :messages="$errors->get('permit_type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="permit_number" :value="__('Numero de permiso')" />
                                <x-text-input id="permit_number" name="permit_number" type="text" class="mt-1 block w-full" value="{{ old('permit_number', $weapon->permit_number) }}" />
                                <x-input-error :messages="$errors->get('permit_number')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="permit_expires_at" :value="__('Fecha de vencimiento')" />
                                <x-text-input id="permit_expires_at" name="permit_expires_at" type="date" class="mt-1 block w-full" value="{{ old('permit_expires_at', optional($weapon->permit_expires_at)->format('Y-m-d')) }}" />
                                <x-input-error :messages="$errors->get('permit_expires_at')" class="mt-2" />
                            </div>

                            <div class="md:col-start-2 md:row-start-1 md:row-span-3 flex flex-col">
                                <x-input-label for="permit_photo" :value="__('Foto del permiso')" />
                                <label for="permit_photo" class="mt-1 block h-full cursor-pointer">
                                    <input id="permit_photo" name="permit_photo" type="file" accept="image/*" class="hidden"
                                        data-preview-target="permit_preview" data-placeholder-target="permit_placeholder" />
                                    <div class="flex h-full min-h-[12rem] w-full items-center justify-center rounded border border-dashed border-gray-300 bg-gray-50 text-xs text-gray-500">
                                        <span id="permit_placeholder">{{ __('Seleccionar foto') }}</span>
                                        <img id="permit_preview" alt="Previsualizacion"
                                            class="hidden h-full w-full rounded object-cover" />
                                    </div>
                                </label>
                                <x-input-error :messages="$errors->get('permit_photo')" class="mt-2" />
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="notes" :value="__('Notas')" />
                            <textarea id="notes" name="notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $weapon->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2 flex justify-end gap-2">
                            <a href="{{ route('weapons.show', $weapon) }}" class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Guardar cambios') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

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

<link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css">
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

    const openEditor = (input) => {
        const file = input.files && input.files[0];
        if (!file) {
            return;
        }

        activeInput = input;
        modalImage.src = URL.createObjectURL(file);
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

    const closeEditor = () => {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modalImage.removeAttribute('src');
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
            if (preview && placeholder) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }

            closeEditor();
        }, 'image/jpeg', 0.92);
    };

    closeButton.addEventListener('click', closeEditor);
    cancelButton.addEventListener('click', closeEditor);
    cropButton.addEventListener('click', applyCrop);
    rotateLeftButton.addEventListener('click', () => cropper && cropper.rotate(-90));
    rotateRightButton.addEventListener('click', () => cropper && cropper.rotate(90));

    document.querySelectorAll('input[data-preview-target]').forEach((input) => {
        input.addEventListener('change', () => openEditor(input));
    });
</script>
