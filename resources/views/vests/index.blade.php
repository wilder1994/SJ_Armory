<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Chalecos') }}</h2>
                <p class="sj-section-header__subtitle">{{ __('Control de inventario y semaforización de chalecos') }}</p>
            </div>
            <div class="sj-section-header__actions">
                @can('import', App\Models\Vest::class)
                    <a href="{{ route('vest-imports.index') }}" class="sj-ui-btn sj-ui-btn--ghost">
                        {{ __('Subir Excel') }}
                    </a>
                @endcan
                @can('create', App\Models\Vest::class)
                    <a href="{{ route('vests.create') }}" class="sj-ui-btn sj-ui-btn--primary">
                        {{ __('Nuevo chaleco') }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide">
            @if (session('status'))
                <div class="mb-4 rounded bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="sj-ui-kpi-grid mb-6">
                @foreach ($alertLabels as $key => $label)
                    @php
                        $isActive = ($filters['alert'] ?? null) === $key || ($key === 'all' && empty($filters['alert']));
                        $toneClass = match ($key) {
                            'vigent' => 'sj-ui-kpi--green',
                            'preventive' => 'sj-ui-kpi--amber',
                            'critical' => 'sj-ui-kpi--orange',
                            'expired' => 'sj-ui-kpi--red',
                            'unassigned' => 'sj-ui-kpi--slate',
                            default => 'sj-ui-kpi--blue',
                        };
                        $href = $key === 'all'
                            ? route('vests.index', array_filter(collect($filters)->except('alert')->all()))
                            : route('vests.index', array_merge(array_filter(collect($filters)->except('alert')->all()), ['alert' => $key]));
                    @endphp
                    <a href="{{ $href }}" class="sj-ui-kpi {{ $toneClass }} @if($isActive) is-active @endif">
                        <span class="sj-ui-kpi__label">{{ $label }}</span>
                        <span class="sj-ui-kpi__value">{{ $kpiCounts[$key] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            <div class="sj-ui-card overflow-hidden">
                <div class="sj-ui-card__body p-6">
                    <form method="GET" action="{{ route('vests.index') }}" class="sj-ui-filter-bar">
                        @if (! empty($filters['alert']))
                            <input type="hidden" name="alert" value="{{ $filters['alert'] }}">
                        @endif
                        <div class="sj-ui-filter-bar__fields">
                            <div class="sj-ui-field w-40 shrink-0">
                                <label for="vest-filter-q" class="sj-ui-field__label">{{ __('Buscar') }}</label>
                                <input id="vest-filter-q" type="text" name="q" value="{{ $filters['q'] }}" class="sj-ui-field__control" placeholder="{{ __('Serie, marca, trabajador...') }}">
                            </div>
                            <div class="sj-ui-field min-w-[10rem] flex-1">
                                <label for="vest-filter-client" class="sj-ui-field__label">{{ __('Cliente') }}</label>
                                <select id="vest-filter-client" name="client_id" class="sj-ui-field__control">
                                    <option value="">{{ __('Todos') }}</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" @selected($filters['client_id'] == $client->id)>{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sj-ui-field w-36 shrink-0">
                                <label for="vest-filter-assigned" class="sj-ui-field__label">{{ __('Asignación') }}</label>
                                <select id="vest-filter-assigned" name="assigned" class="sj-ui-field__control">
                                    <option value="">{{ __('Todos') }}</option>
                                    <option value="yes" @selected($filters['assigned'] === 'yes')>{{ __('Asignados') }}</option>
                                    <option value="no" @selected($filters['assigned'] === 'no')>{{ __('Sin asignar') }}</option>
                                </select>
                            </div>
                            <div class="sj-ui-field w-36 shrink-0">
                                <label for="vest-filter-brand" class="sj-ui-field__label">{{ __('Marca') }}</label>
                                <input id="vest-filter-brand" type="text" name="brand" value="{{ $filters['brand'] }}" class="sj-ui-field__control">
                            </div>
                            <div class="sj-ui-filter-bar__actions">
                                <a href="{{ route('vests.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Limpiar') }}</a>
                                <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Filtrar') }}</button>
                            </div>
                        </div>
                    </form>

                    <div class="sj-table-wrap overflow-x-auto">
                        <table class="sj-table sj-table--align-left min-w-full text-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Serie') }}</th>
                                    <th>{{ __('Marca') }}</th>
                                    <th>{{ __('Trabajador') }}</th>
                                    <th>{{ __('Cliente') }}</th>
                                    <th>{{ __('Puesto') }}</th>
                                    <th>{{ __('Vence') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th>{{ __('Fotos') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($vests as $vest)
                                    @php $alert = \App\Support\VestAlert::forVest($vest); @endphp
                                    <tr class="{{ $alert['row_class'] }}">
                                        <td class="font-medium">{{ $vest->serial_number }}</td>
                                        <td>{{ $vest->brand ?: '—' }}</td>
                                        <td>{{ $vest->worker?->name ?? __('Sin asignar') }}</td>
                                        <td>{{ $vest->client?->name ?? '—' }}</td>
                                        <td>{{ $vest->post?->name ?? '—' }}</td>
                                        <td>{{ $vest->expires_at?->format('d/m/Y') ?? '—' }}</td>
                                        <td><span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $alert['badge_class'] }}">{{ $alert['state'] }}</span></td>
                                        <td>{{ $vest->photos_count }}/4</td>
                                        <td class="text-right">
                                            <a href="{{ route('vests.show', $vest) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">{{ __('Ver') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-8 text-center text-gray-500">{{ __('No hay chalecos para los filtros seleccionados.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $vests->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
