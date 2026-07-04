<section class="sj-ui-card p-5">
    <div class="flex items-center justify-between gap-4 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ __('Fotografías del chaleco') }}</h3>
            <p class="text-sm text-gray-500">{{ __('4 fotos: 2 vistas completas y 2 placas de serie. Al actualizar, la foto anterior se elimina.') }}</p>
        </div>
        <span class="text-sm font-semibold text-gray-600">{{ $photosByDescription->count() }}/4</span>
    </div>

    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        @foreach (\App\Models\VestPhoto::DESCRIPTIONS as $description => $label)
            @php
                $photo = $photosByDescription->get($description);
                $file = $photo?->file;
            @endphp
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</div>
                <div class="aspect-[4/5] overflow-hidden rounded-md border border-dashed border-gray-300 bg-white flex items-center justify-center">
                    @if ($file)
                        <img src="{{ asset('storage/'.$file->path) }}" alt="{{ $label }}" class="h-full w-full object-cover">
                    @else
                        <span class="text-xs text-gray-400 px-2 text-center">{{ __('Sin foto') }}</span>
                    @endif
                </div>

                @can('updatePhotos', $vest)
                    <form method="POST" action="{{ route('vests.photos.store', $vest) }}" enctype="multipart/form-data" class="mt-3 space-y-2">
                        @csrf
                        <input type="hidden" name="description" value="{{ $description }}">
                        <input type="file" name="photo" accept="image/*" required class="block w-full text-xs text-gray-600 file:mr-2 file:rounded file:border-0 file:bg-indigo-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-indigo-700">
                        <button type="submit" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--xs sj-ui-btn--block">
                            {{ $photo ? __('Reemplazar') : __('Subir') }}
                        </button>
                    </form>
                    @if ($photo)
                        <form method="POST" action="{{ route('vests.photos.destroy', [$vest, $photo]) }}" onsubmit="return confirm(@json(__('¿Eliminar esta foto?')));" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--xs sj-ui-btn--block sj-ui-btn--danger">{{ __('Eliminar') }}</button>
                        </form>
                    @endif
                @endcan
            </div>
        @endforeach
    </div>
</section>
