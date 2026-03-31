<x-app-layout>
    <x-slot name="header">
        @php
            $selectedYear = $filters['year'] ?? null;
        @endphp

        <div class="sj-incident-header">
            <div class="sj-incident-header__main">
                <p class="sj-incident-header__eyebrow">{{ __('Centro de reportes') }}</p>
                <h2 class="sj-incident-header__title">
                    {{ $selectedType ? __('Novedades: ') . $selectedType->name : __('Novedades operativas') }}
                </h2>
                <p class="sj-incident-header__subtitle">
                    {{ __('Consolidado histórico por tipo, modalidad y arma para seguimiento gerencial.') }}
                </p>
            </div>

            <div class="sj-incident-header__side">
                <div class="sj-incident-header__actions">
                    <a href="{{ route('reports.index') }}" class="sj-incident-header__button sj-incident-header__button--ghost">
                        {{ __('Volver a reportes') }}
                    </a>

                    <form
                        method="GET"
                        action="{{ $selectedType ? route('reports.weapon-incidents.show', $selectedType) : route('reports.weapon-incidents.index') }}"
                        class="sj-incident-header__filter"
                    >
                        <div class="sj-incident-header__filter-group">
                            <span class="sj-incident-header__inline-label">{{ __('Año') }}</span>
                            <select id="incident-year" name="year" class="sj-dashboard-filter__select sj-incident-header__control">
                                <option value="" @selected($selectedYear === null)>{{ __('Todos') }}</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" @selected($selectedYear === (int) $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <a href="{{ $selectedType ? route('reports.weapon-incidents.show', $selectedType) : route('reports.weapon-incidents.index') }}" class="sj-incident-header__button sj-incident-header__button--ghost">
                            {{ __('Limpiar') }}
                        </a>
                        <button type="submit" class="sj-incident-header__button sj-incident-header__button--primary">
                            {{ __('Aplicar') }}
                        </button>
                    </form>

                    @can('create', App\Models\WeaponIncident::class)
                        <button type="button" class="sj-incident-header__button sj-incident-header__button--accent" data-open-modal="weapon-incident-modal">
                            {{ __('Agregar reporte') }}
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </x-slot>

    <div
        class="py-8"
        data-incident-module
        data-incident-modality-map='@json($modalityMap)'
        data-open-create-modal="{{ old('incident_type_id') && !old('focus_incident_id') ? '1' : '0' }}"
        data-open-incident-case="{{ old('focus_incident_id', request('focus_incident')) }}"
    >
        <div class="sj-report-shell mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-xl bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @include('reports.weapon-incidents.base')
        </div>
    </div>

    @can('create', App\Models\WeaponIncident::class)
        @include('reports.weapon-incidents.modal')
    @endcan
</x-app-layout>
