<a href="{{ route('weapon-imports.show', $batch) }}"
    class="block rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="truncate text-base font-semibold text-gray-800">{{ $batch->source_name }}</div>
            <div class="mt-1 text-sm text-gray-500">{{ $batch->created_at?->format('d/m/Y H:i') }}</div>
        </div>
        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
            Ejecutado
        </span>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-gray-600">
        <div>
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total</div>
            <div class="mt-1 font-semibold text-gray-800">{{ $batch->total_rows }}</div>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Errores</div>
            <div class="mt-1 font-semibold text-gray-800">{{ $batch->error_count }}</div>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Crear</div>
            <div class="mt-1 font-semibold text-blue-700">{{ $batch->create_count }}</div>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Actualizar</div>
            <div class="mt-1 font-semibold text-amber-700">{{ $batch->update_count }}</div>
        </div>
    </div>
</a>
