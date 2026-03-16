<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Subir armas</h2>
                <p class="text-sm text-gray-500">Carga masiva con validacion previa antes de crear o actualizar armas.</p>
            </div>
            <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'weapon-import-upload')"
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                Subir documento
            </button>
        </div>
    </x-slot>

    @php
        $recentBatches = $batches
            ->filter(fn ($batch) => $batch->isExecuted())
            ->reject(fn ($batch) => $batch->id === $selectedBatch?->id)
            ->values();
    @endphp

    <div class="py-8">
        <div class="w-full px-4 pb-20 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('batch'))
                <div class="mb-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first('batch') }}
                </div>
            @endif

            <div class="grid gap-6 {{ $recentBatches->isNotEmpty() ? 'lg:grid-cols-3' : '' }}">
                @if ($recentBatches->isNotEmpty())
                    <aside class="space-y-6 lg:col-span-1">
                        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Lotes ejecutados</h3>
                            <div class="mt-4 space-y-3">
                                @foreach ($recentBatches as $batch)
                                    <a href="{{ route('weapon-imports.index', ['batch' => $batch->id]) }}"
                                        class="block rounded-lg border border-gray-200 bg-white px-4 py-3 transition hover:border-gray-300">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-semibold text-gray-800">{{ $batch->source_name }}</div>
                                                <div class="mt-1 text-xs text-gray-500">{{ $batch->created_at?->format('d/m/Y H:i') }}</div>
                                            </div>
                                            <span class="rounded-full bg-green-100 px-2 py-1 text-[11px] font-semibold text-green-700">
                                                Ejecutado
                                            </span>
                                        </div>
                                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-gray-600">
                                            <div>Total: {{ $batch->total_rows }}</div>
                                            <div>Errores: {{ $batch->error_count }}</div>
                                            <div>Crear: {{ $batch->create_count }}</div>
                                            <div>Actualizar: {{ $batch->update_count }}</div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </aside>
                @endif

                <section class="space-y-6 {{ $recentBatches->isNotEmpty() ? 'lg:col-span-2' : '' }}">
                    @if ($selectedBatch)
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">{{ $selectedBatch->source_name }}</h3>
                                    <div class="mt-1 text-sm text-gray-500">
                                        Subido por {{ $selectedBatch->uploadedBy?->name ?? 'Sistema' }}
                                        el {{ $selectedBatch->created_at?->format('d/m/Y H:i') }}
                                    </div>
                                    @if ($selectedBatch->isExecuted() && $selectedBatch->executed_at)
                                        <div class="mt-1 text-sm text-gray-500">
                                            Ejecutado por {{ $selectedBatch->executedBy?->name ?? 'Sistema' }}
                                            el {{ $selectedBatch->executed_at->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $selectedBatch->isExecuted() ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $selectedBatch->isExecuted() ? 'Lote ejecutado' : 'Lote pendiente' }}
                                    </span>
                                    @if ($selectedBatch->isDraft())
                                        <a href="{{ route('weapon-imports.index', ['batch' => $selectedBatch->id, 'preview' => 1]) }}"
                                            class="inline-flex items-center rounded-md border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50">
                                            Revisar lote
                                        </a>
                                        <form method="POST" action="{{ route('weapon-imports.execute', $selectedBatch) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-gray-300"
                                                @disabled($selectedBatch->hasErrors())>
                                                Ejecutar
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('weapon-imports.discard', $selectedBatch) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center rounded-md border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                                                Cancelar carga
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            @if ($selectedBatch->isExecuted())
                                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                    <div class="rounded-lg bg-blue-50 px-4 py-3">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">Crear</div>
                                        <div class="mt-1 text-2xl font-semibold text-blue-900">{{ $selectedBatch->create_count }}</div>
                                    </div>
                                    <div class="rounded-lg bg-amber-50 px-4 py-3">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-amber-700">Actualizar</div>
                                        <div class="mt-1 text-2xl font-semibold text-amber-900">{{ $selectedBatch->update_count }}</div>
                                    </div>
                                    <div class="rounded-lg bg-green-50 px-4 py-3">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-green-700">Sin cambios</div>
                                        <div class="mt-1 text-2xl font-semibold text-green-900">{{ $selectedBatch->no_change_count }}</div>
                                    </div>
                                    <div class="rounded-lg bg-rose-50 px-4 py-3">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-rose-700">Errores</div>
                                        <div class="mt-1 text-2xl font-semibold text-rose-900">{{ $selectedBatch->error_count }}</div>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    @include('weapon-imports.partials.rows', ['rows' => $selectedBatch->rows])
                                </div>
                            @else
                                <div class="mt-5 rounded-lg border border-dashed border-gray-200 bg-gray-50 px-4 py-5 text-sm text-gray-600">
                                    Este lote esta pendiente. Usa <strong>Revisar lote</strong> para validar la carga antes de ejecutarla.
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="rounded-lg border border-dashed border-gray-200 bg-white p-10 text-center text-gray-500 shadow-sm">
                            Sube tu primer archivo para revisar y ejecutar cambios masivos de armas.
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>

    <x-modal name="weapon-import-upload" :show="$errors->has('document')" maxWidth="2xl" focusable>
        <div id="weapon-import-upload-modal" class="p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Subir documento</h3>
                    <p class="mt-1 text-sm text-gray-500">Arrastra un archivo, pegalo desde el portapapeles o selecciona uno de este equipo.</p>
                </div>
                <button type="button" x-data="" x-on:click.prevent="$dispatch('close-modal', 'weapon-import-upload')"
                    class="rounded-md px-2 py-1 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-700">
                    Cerrar
                </button>
            </div>

            <form method="POST" action="{{ route('weapon-imports.preview') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf

                <input id="weapon-import-document" type="file" name="document" accept=".xlsx,.csv,.txt" class="hidden" required>

                <label for="weapon-import-document" id="weapon-import-dropzone"
                    class="flex min-h-[15rem] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center transition hover:border-indigo-300 hover:bg-indigo-50/40">
                    <div class="rounded-full bg-white p-4 text-indigo-600 shadow-sm">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path d="M12 16V4" />
                            <path d="M8 8l4-4 4 4" />
                            <path d="M4 16.5v1.5A2 2 0 006 20h12a2 2 0 002-2v-1.5" />
                        </svg>
                    </div>
                    <div class="mt-4 text-base font-semibold text-gray-800">Arrastra el documento aqui</div>
                    <div class="mt-2 text-sm text-gray-500">Tambien puedes pegar el archivo con Ctrl + V o seleccionarlo manualmente.</div>
                    <div class="mt-5 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm">
                        Seleccionar de este equipo
                    </div>
                    <div id="weapon-import-file-name" class="mt-4 text-sm font-medium text-indigo-700"></div>
                </label>

                <x-input-error :messages="$errors->get('document')" class="mt-2" />

                <div class="flex items-center justify-end gap-3">
                    <button type="button" x-data="" x-on:click.prevent="$dispatch('close-modal', 'weapon-import-upload')"
                        class="rounded-md px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 hover:text-gray-800">
                        Cancelar
                    </button>
                    <x-primary-button id="weapon-import-submit" disabled>Subir</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    @if ($openPreview && $selectedBatch?->isDraft())
        <div id="weapon-import-preview-root" aria-modal="true" role="dialog">
            <div style="position: fixed; left: 0; right: 0; top: 64px; bottom: 0; z-index: 5000; background: rgba(15, 23, 42, 0.55);">
                <div style="height: 100%; width: 100%; padding: 16px; box-sizing: border-box; display: flex; align-items: stretch; justify-content: center;">
                    <div style="width: min(1400px, 100%); height: 100%; background: #ffffff; border-radius: 16px; box-shadow: 0 24px 48px rgba(15, 23, 42, 0.22); display: flex; flex-direction: column; overflow: hidden;">
                        <div style="flex: 0 0 auto; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 20px 24px; border-bottom: 1px solid #e5e7eb; background: #ffffff;">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Revision del lote</h3>
                                <p class="mt-1 text-sm text-gray-500">Verifica la accion de cada fila antes de ejecutar los cambios.</p>
                            </div>
                            <form method="POST" action="{{ route('weapon-imports.discard', $selectedBatch) }}">
                                @csrf
                                <button type="submit"
                                    class="rounded-md px-2 py-1 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-700">
                                    Cerrar
                                </button>
                            </form>
                        </div>

                        <div id="weapon-import-preview-scroll" style="flex: 1 1 auto; min-height: 0; overflow: auto; padding: 16px 24px;">
                            @include('weapon-imports.partials.rows', ['rows' => $selectedBatch->rows])
                        </div>

                        <div style="flex: 0 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 24px; border-top: 1px solid #e5e7eb; background: #ffffff;">
                            <div class="text-sm {{ $selectedBatch->hasErrors() ? 'text-rose-700' : 'text-green-700' }}">
                                {{ $selectedBatch->hasErrors()
                                    ? 'Hay filas con error. No puedes ejecutar este lote.'
                                    : 'No hay errores. Puedes ejecutar el lote.' }}
                            </div>

                            <div class="flex items-center gap-3">
                                <form method="POST" action="{{ route('weapon-imports.discard', $selectedBatch) }}">
                                    @csrf
                                    <button type="submit"
                                        class="rounded-md px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 hover:text-gray-800">
                                        Cancelar
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('weapon-imports.execute', $selectedBatch) }}">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-gray-300"
                                        @disabled($selectedBatch->hasErrors())>
                                        Ejecutar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>

<script>
    (() => {
        const input = document.getElementById('weapon-import-document');
        const dropzone = document.getElementById('weapon-import-dropzone');
        const fileName = document.getElementById('weapon-import-file-name');
        const submit = document.getElementById('weapon-import-submit');
        const uploadModal = document.getElementById('weapon-import-upload-modal');

        if (!input || !dropzone || !fileName || !submit || !uploadModal) {
            return;
        }

        const setFile = (file) => {
            if (!file) {
                input.value = '';
                fileName.textContent = '';
                submit.disabled = true;
                return;
            }

            const transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
            fileName.textContent = file.name;
            submit.disabled = false;
        };

        input.addEventListener('change', () => {
            const file = input.files && input.files[0] ? input.files[0] : null;
            setFile(file);
        });

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.add('border-indigo-400', 'bg-indigo-50');
            });
        });

        ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.remove('border-indigo-400', 'bg-indigo-50');
            });
        });

        dropzone.addEventListener('drop', (event) => {
            const file = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files[0] : null;
            if (file) {
                setFile(file);
            }
        });

        window.addEventListener('paste', (event) => {
            const isVisible = uploadModal.offsetParent !== null;
            if (!isVisible) {
                return;
            }

            const items = event.clipboardData ? Array.from(event.clipboardData.items || []) : [];
            const fileItem = items.find((item) => item.kind === 'file');
            const file = fileItem ? fileItem.getAsFile() : null;

            if (file) {
                event.preventDefault();
                setFile(file);
            }
        });
    })();

    (() => {
        const root = document.getElementById('weapon-import-preview-root');
        if (!root) {
            return;
        }

        document.body.appendChild(root);
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';

        const content = document.querySelector('.sj-content');
        if (content) {
            content.style.overflow = 'hidden';
        }

        const scroll = document.getElementById('weapon-import-preview-scroll');
        if (scroll) {
            scroll.scrollTop = 0;
        }
    })();
</script>