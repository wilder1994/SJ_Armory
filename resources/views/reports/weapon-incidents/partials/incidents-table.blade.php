<div class="max-h-[min(65vh,620px)] overflow-auto rounded-lg border border-gray-100">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="sticky top-0 z-10 bg-gray-50 shadow-sm">
            <tr>
                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Fecha') }}</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Arma') }}</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Tipo') }}</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Modalidad') }}</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Estado') }}</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Cliente') }}</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Resumen') }}</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Seguimiento') }}</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Adjunto') }}</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Expediente') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($incidents as $incident)
                @php
                    $updateCount = $incident->updates->reject(
                        fn (App\Models\WeaponIncidentUpdate $update) => $update->event_type === App\Models\WeaponIncidentUpdate::EVENT_REPORTED
                    )->count();
                    $statusTone = match ($incident->status) {
                        App\Models\WeaponIncident::STATUS_OPEN => 'danger',
                        App\Models\WeaponIncident::STATUS_IN_PROGRESS => 'warning',
                        App\Models\WeaponIncident::STATUS_RESOLVED => 'ok',
                        App\Models\WeaponIncident::STATUS_CANCELLED => 'neutral',
                        default => 'notice',
                    };
                @endphp
                <tr
                    class="hover:bg-gray-50/80"
                    x-show="!q.trim() || $el.innerText.toLowerCase().includes(q.trim().toLowerCase())"
                >
                    <td class="px-3 py-2">{{ $incident->event_at?->format('Y-m-d H:i') ?? '-' }}</td>
                    <td class="px-3 py-2">
                        <a href="{{ route('weapons.show', $incident->weapon) }}" class="text-indigo-600 hover:text-indigo-900">
                            {{ $incident->weapon?->serial_number ?? '-' }}
                        </a>
                    </td>
                    <td class="px-3 py-2">{{ $incident->type?->name ?? '-' }}</td>
                    <td class="px-3 py-2">{{ $incident->modality?->name ?? '-' }}</td>
                    <td class="px-3 py-2">
                        <span class="weapon-incident-status">
                            <span class="weapon-incident-status__dot weapon-incident-status__dot--{{ $statusTone }}"></span>
                            <span>{{ $statusOptions[$incident->status] ?? $incident->status }}</span>
                        </span>
                    </td>
                    <td class="px-3 py-2">{{ $incident->weapon?->activeClientAssignment?->client?->name ?? '-' }}</td>
                    <td class="px-3 py-2">{{ $incident->observation ?? ($incident->note ?? '-') }}</td>
                    <td class="px-3 py-2">
                        <div class="text-slate-700">{{ $updateCount }} {{ __('hitos') }}</div>
                        <div class="text-xs text-slate-500">
                            {{ $incident->latestUpdate?->eventTypeLabel() ?? __('Solo reporte inicial') }}
                            @if ($incident->latestActivityAt())
                                &middot; {{ $incident->latestActivityAt()->format('Y-m-d H:i') }}
                            @endif
                        </div>
                    </td>
                    <td class="px-3 py-2">
                        @if ($incident->attachmentFile)
                            <a href="{{ route('weapon-incidents.attachment', $incident) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ __('Descargar') }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50"
                            data-open-modal="incident-case-{{ $incident->id }}"
                        >
                            @can('update', $incident)
                                {{ __('Gestionar') }}
                            @else
                                {{ __('Ver caso') }}
                            @endcan
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-3 py-6 text-center text-gray-500">
                        {{ __('No hay novedades con los filtros actuales.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
