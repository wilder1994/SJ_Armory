@php
    $actionStyles = [
        'create' => [
            'row' => 'bg-blue-50',
            'badge' => 'bg-blue-100 text-blue-700',
            'dot' => 'bg-blue-500',
            'label' => 'Crear',
        ],
        'no_change' => [
            'row' => 'bg-green-50',
            'badge' => 'bg-green-100 text-green-700',
            'dot' => 'bg-green-500',
            'label' => 'Sin cambios',
        ],
        'update' => [
            'row' => 'bg-amber-50',
            'badge' => 'bg-amber-100 text-amber-700',
            'dot' => 'bg-amber-500',
            'label' => 'Actualizar',
        ],
        'error' => [
            'row' => 'bg-rose-50',
            'badge' => 'bg-rose-100 text-rose-700',
            'dot' => 'bg-rose-500',
            'label' => 'Error',
        ],
    ];
@endphp

<div class="space-y-4">
    <div class="flex flex-wrap gap-3 text-sm">
        @foreach (['create', 'no_change', 'update', 'error'] as $actionKey)
            @php
                $style = $actionStyles[$actionKey];
                $count = $rows->where('action', $actionKey)->count();
            @endphp
            <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-gray-700">
                <span class="h-3 w-3 rounded-full {{ $style['dot'] }}"></span>
                <span>{{ $style['label'] }}</span>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ $count }}</span>
            </div>
        @endforeach
    </div>

    <div class="rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="sticky top-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium text-gray-600">Fila</th>
                    <th class="sticky top-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium text-gray-600">Accion</th>
                    <th class="sticky top-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium text-gray-600">Tipo</th>
                    <th class="sticky top-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium text-gray-600">Marca</th>
                    <th class="sticky top-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium text-gray-600">Serie</th>
                    <th class="sticky top-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium text-gray-600">Calibre</th>
                    <th class="sticky top-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium text-gray-600">Capacidad</th>
                    <th class="sticky top-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium text-gray-600">Tipo permiso</th>
                    <th class="sticky top-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium text-gray-600">No. permiso</th>
                    <th class="sticky top-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium text-gray-600">Fecha vencimiento</th>
                    <th class="sticky top-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium text-gray-600">Observacion</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($rows as $row)
                    @php
                        $style = $actionStyles[$row->action] ?? $actionStyles['error'];
                        $raw = $row->raw_payload ?? [];
                        $normalized = $row->normalized_payload ?? [];
                        $dateValue = $normalized['permit_expires_at'] ?? null;
                        $formattedDate = $dateValue ? \Illuminate\Support\Carbon::parse($dateValue)->format('d/m/Y') : ($raw['permit_expires_at'] ?? '');
                    @endphp
                    <tr class="{{ $style['row'] }}">
                        <td class="px-3 py-2 font-medium text-gray-800">{{ $row->row_number }}</td>
                        <td class="px-3 py-2">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $style['badge'] }}">
                                {{ $row->actionLabel() }}
                            </span>
                        </td>
                        <td class="px-3 py-2">{{ $normalized['weapon_type'] ?? $raw['weapon_type'] ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $normalized['brand'] ?? $raw['brand'] ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $normalized['serial_number'] ?? $raw['serial_number'] ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $normalized['caliber'] ?? $raw['caliber'] ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $normalized['capacity'] ?? $raw['capacity'] ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $normalized['permit_type'] ?? $raw['permit_type'] ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $normalized['permit_number'] ?? $raw['permit_number'] ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $formattedDate ?: '-' }}</td>
                        <td class="px-3 py-2 text-gray-700">
                            <div>{{ $row->summary ?: '-' }}</div>
                            @if (!empty($row->errors))
                                <div class="mt-1 text-xs text-rose-700">{{ implode(' ', $row->errors) }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-3 py-6 text-center text-sm text-gray-500">No hay filas para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>