@php
    $selectedYear = $filters['year'] ?? null;
    $typeChartItems = collect($dashboard['type_chart']['items'])
        ->filter(fn ($item) => ($item['value'] ?? 0) > 0)
        ->values();
    $modalityChartItems = collect($dashboard['modality_chart']['items'])
        ->filter(fn ($item) => ($item['value'] ?? 0) > 0)
        ->values();
@endphp

<section class="sj-dashboard-kpis sj-dashboard-kpis--compact">
    @foreach ($dashboard['kpis'] as $item)
        <article class="sj-kpi-card sj-kpi-card--{{ $item['tone'] }}">
            <div class="sj-kpi-card__label">{{ $item['label'] }}</div>
            <div class="sj-kpi-card__value">{{ number_format($item['value']) }}</div>
            <div class="sj-kpi-card__helper">{{ $item['helper'] }}</div>
        </article>
    @endforeach
</section>

<section class="sj-dashboard-grid sj-dashboard-grid--primary">
    <article class="sj-panel sj-panel--donut">
        <div class="sj-panel__head">
            <div>
                <div class="sj-form-section__title">{{ __('Tipos') }}</div>
                <h2 class="sj-panel__title">{{ __('Novedades por tipo') }}</h2>
            </div>
        </div>

        <div class="sj-donut-card sj-donut-card--stacked">
            <div class="sj-donut-card__chart sj-donut-card__chart--stacked">
                <div class="sj-donut-chart-shell">
                    <div class="sj-donut-chart sj-donut-chart--executive" style="--donut-gradient: {{ $dashboard['type_chart']['gradient'] }};">
                        <div class="sj-donut-chart__center sj-donut-chart__center--executive">
                            <span class="sj-donut-chart__label">{{ $dashboard['type_chart']['center_label'] }}</span>
                            <strong class="sj-donut-chart__value">{{ number_format($dashboard['type_chart']['center_value']) }}</strong>
                        </div>

                        @foreach ($typeChartItems as $item)
                            @if (!empty($item['show_arc_label']))
                                <span class="sj-donut-chart__arc-label" style="left: {{ $item['arc_x'] }}%; top: {{ $item['arc_y'] }}%;">
                                    <span class="sj-donut-chart__arc-share">{{ $item['share_label'] }}</span>
                                    <span class="sj-donut-chart__arc-value">{{ number_format($item['value']) }}</span>
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="sj-donut-card__legend sj-donut-card__legend--stacked">
                @forelse ($typeChartItems as $item)
                    @php
                        $typeRoute = ['incidentType' => $item['code']];
                        if ($selectedYear !== null) {
                            $typeRoute['year'] = $selectedYear;
                        }
                    @endphp
                    <a
                        href="{{ route('reports.weapon-incidents.show', $typeRoute) }}"
                        class="sj-donut-legend__row {{ $item['selected'] ? 'sj-donut-legend__row--active' : '' }}"
                    >
                        <span class="sj-donut-legend__swatch" style="background: {{ $item['color'] }};"></span>
                        <span class="sj-donut-legend__text">{{ $item['label'] }}</span>
                        @if (empty($item['show_arc_label']))
                            <span class="sj-donut-legend__meta">
                                <span class="sj-donut-legend__share">{{ $item['share_label'] }}</span>
                                <span class="sj-donut-legend__count">{{ number_format($item['value']) }}</span>
                            </span>
                        @endif
                    </a>
                @empty
                    <p class="sj-panel__empty sj-panel__empty--soft">{{ __('No hay tipos con datos para el filtro actual.') }}</p>
                @endforelse
            </div>
        </div>
    </article>

    <article class="sj-panel sj-panel--donut">
        <div class="sj-panel__head">
            <div>
                <div class="sj-form-section__title">{{ __('Modalidades') }}</div>
                <h2 class="sj-panel__title">
                    {{ $selectedType ? __('Distribución de modalidades') : __('Distribución actual') }}
                </h2>
            </div>
        </div>

        <div class="sj-donut-card sj-donut-card--stacked">
            <div class="sj-donut-card__chart sj-donut-card__chart--stacked">
                <div class="sj-donut-chart-shell">
                    <div class="sj-donut-chart sj-donut-chart--executive" style="--donut-gradient: {{ $dashboard['modality_chart']['gradient'] }};">
                        <div class="sj-donut-chart__center sj-donut-chart__center--executive">
                            <span class="sj-donut-chart__label">{{ $dashboard['modality_chart']['center_label'] }}</span>
                            <strong class="sj-donut-chart__value">{{ number_format($dashboard['modality_chart']['center_value']) }}</strong>
                        </div>

                        @foreach ($modalityChartItems as $item)
                            @if (!empty($item['show_arc_label']))
                                <span class="sj-donut-chart__arc-label" style="left: {{ $item['arc_x'] }}%; top: {{ $item['arc_y'] }}%;">
                                    <span class="sj-donut-chart__arc-share">{{ $item['share_label'] }}</span>
                                    <span class="sj-donut-chart__arc-value">{{ number_format($item['value']) }}</span>
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="sj-donut-card__legend sj-donut-card__legend--stacked">
                @forelse ($modalityChartItems as $item)
                    <div class="sj-donut-legend__row sj-donut-legend__row--static">
                        <span class="sj-donut-legend__swatch" style="background: {{ $item['color'] }};"></span>
                        <span class="sj-donut-legend__text">{{ $item['label'] }}</span>
                        @if (empty($item['show_arc_label']))
                            <span class="sj-donut-legend__meta">
                                <span class="sj-donut-legend__share">{{ $item['share_label'] }}</span>
                                <span class="sj-donut-legend__count">{{ number_format($item['value']) }}</span>
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="sj-panel__empty sj-panel__empty--soft">{{ __('No hay modalidades para el filtro actual.') }}</p>
                @endforelse
            </div>
        </div>
    </article>
</section>

<section class="sj-dashboard-grid sj-dashboard-grid--secondary">
    <article class="sj-panel">
        <div class="sj-panel__head">
            <div>
                <div class="sj-form-section__title">{{ __('Tendencia') }}</div>
                <h2 class="sj-panel__title">{{ $dashboard['timeline_chart']['title'] }}</h2>
            </div>
        </div>

        <div class="sj-column-chart-wrap">
            <div class="sj-column-chart">
                @foreach ($dashboard['timeline_chart']['items'] as $item)
                    <div class="sj-column-chart__item">
                        <div class="sj-column-chart__value">{{ number_format($item['value']) }}</div>
                        <div class="sj-column-chart__track">
                            <div class="sj-column-chart__bar" style="height: {{ max(1, round(($item['value'] / max(1, $dashboard['timeline_chart']['max'])) * 100)) }}%"></div>
                        </div>
                        <div class="sj-column-chart__label">{{ $item['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </article>

    <article class="sj-panel">
        <div class="sj-panel__head">
            <div>
                <div class="sj-form-section__title">{{ __('Recientes') }}</div>
                <h2 class="sj-panel__title">{{ __('Últimos reportes') }}</h2>
            </div>
        </div>

        <div class="sj-stat-list sj-stat-list--compact">
            @forelse ($dashboard['recent_items'] as $item)
                <div class="sj-stat-list__item">
                    <div class="sj-stat-list__meta">
                        <span class="sj-stat-list__dot" style="background:#0f172a"></span>
                        <span class="sj-stat-list__label">{{ $item['label'] }} / {{ $item['weapon'] }}</span>
                    </div>
                    <span class="sj-stat-list__value">{{ $item['status'] }}</span>
                </div>
            @empty
                <p class="sj-panel__empty">{{ __('No hay novedades recientes.') }}</p>
            @endforelse
        </div>
    </article>
</section>

@foreach ($incidents as $incident)
    @include('reports.weapon-incidents.partials.case-modal', ['incident' => $incident, 'statusOptions' => $statusOptions])
@endforeach

