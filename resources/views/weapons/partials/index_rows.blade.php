@forelse ($weapons as $weapon)
    @php
        $expiredDocs = $weapon->documents
            ->filter(function ($doc) {
                if (!($doc->is_permit || $doc->is_renewal) || !$doc->valid_until) {
                    return false;
                }

                return now()->startOfDay()->diffInDays($doc->valid_until, false) <= 0;
            })
            ->map(function ($doc) {
                $name = $doc->document_name ?: __('Documento');
                if ($doc->document_number) {
                    $name .= ' #' . $doc->document_number;
                }
                return $name;
            })
            ->values();
        $hasExpiredDocs = $expiredDocs->isNotEmpty();
        $expiredLabel = $expiredDocs->implode(', ');

        $observationDocs = $weapon->documents
            ->filter(function ($doc) {
                return !empty($doc->observations);
            })
            ->map(function ($doc) {
                $name = $doc->document_name ?: __('Documento');
                if ($doc->document_number) {
                    $name .= ' #' . $doc->document_number;
                }
                return $name . ' (' . $doc->observations . ')';
            })
            ->values();
        $hasObservationDocs = $observationDocs->isNotEmpty();
        $observationLabel = $observationDocs->implode(', ');
        $hasInProcess = $weapon->documents->contains(function ($doc) {
            return ($doc->status ?? '') === 'En proceso';
        });
    @endphp
    <tr @class(['bg-red-50' => $hasExpiredDocs || $hasInProcess])>
        <td class="px-3 py-2 whitespace-nowrap">
            <span title="{{ $weapon->internal_code }}">
                {{ \Illuminate\Support\Str::limit($weapon->internal_code, 8) }}
            </span>
        </td>
        <td class="px-3 py-2 min-w-[200px] whitespace-nowrap">{{ $weapon->activeClientAssignment?->client?->name ?? __('Sin destino') }}</td>
        <td class="px-3 py-2 min-w-[200px] whitespace-nowrap">{{ $weapon->activeClientAssignment?->responsible?->name ?? '-' }}</td>
        <td class="px-3 py-2 whitespace-nowrap">{{ $weapon->serial_number }}</td>
        <td class="px-3 py-2 whitespace-nowrap">{{ $weapon->weapon_type }}</td>
        <td class="px-3 py-2 whitespace-nowrap">{{ $weapon->permit_type ?? '-' }}</td>
        <td class="px-3 py-2 whitespace-nowrap">{{ $weapon->permit_number ?? '-' }}</td>
        <td class="px-3 py-2 whitespace-nowrap">{{ $weapon->permit_expires_at?->format('Y-m-d') ?? '-' }}</td>
        <td class="px-3 py-2 whitespace-nowrap">
            @if ($hasExpiredDocs)
                <span title="Documentos vencidos: {{ $expiredLabel }}@if($hasObservationDocs) | Observaciones: {{ $observationLabel }}@endif" class="text-red-700">
                    {{ $expiredDocs->first() }}
                </span>
            @elseif ($hasObservationDocs)
                <span title="Observaciones: {{ $observationLabel }}" class="@if($hasInProcess) text-red-700 @endif">
                    {{ $weapon->documents->firstWhere('observations')?->observations }}
                </span>
            @else
                {{ $weapon->activeClientAssignment ? __('Asignada') : __('Sin destino') }}
            @endif
        </td>
        <td class="px-3 py-2 min-w-[220px] whitespace-nowrap">
            @if ($weapon->activePostAssignment)
                {{ $weapon->activePostAssignment->post?->name }}
            @elseif ($weapon->activeWorkerAssignment)
                {{ $weapon->activeWorkerAssignment->worker?->name }}
            @else
                -
            @endif
        </td>
        <td class="px-3 py-2 whitespace-nowrap">
            {{ $weapon->activeWorkerAssignment?->worker?->document ?? '-' }}
        </td>
        <td class="px-3 py-2 text-right space-x-2 whitespace-nowrap">
            <a href="{{ route('weapons.show', $weapon) }}" class="text-indigo-600 hover:text-indigo-900">
                {{ __('Ver') }}
            </a>
            @can('update', $weapon)
                <a href="{{ route('weapons.edit', $weapon) }}" class="text-indigo-600 hover:text-indigo-900">
                    {{ __('Editar') }}
                </a>
            @endcan
            @can('delete', $weapon)
                <form action="{{ route('weapons.destroy', $weapon) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Eliminar arma?')">
                        {{ __('Eliminar') }}
                    </button>
                </form>
            @endcan
        </td>
    </tr>
@empty
    <tr>
        <td colspan="12" class="px-3 py-6 text-center text-gray-500">
            {{ __('No hay armas registradas.') }}
        </td>
    </tr>
@endforelse
