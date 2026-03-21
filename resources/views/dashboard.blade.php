<x-app-layout>
    <div
        class="sj-dashboard py-8"
        x-data="dashboardMonitor({ initialData: @js($dashboard), dataUrl: '{{ route('dashboard.metrics') }}' })"
        x-init="init()"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <section class="sj-dashboard-hero">
                <div>
                    <p class="sj-dashboard-hero__eyebrow">Centro de monitoreo</p>
                    <h1 class="sj-dashboard-hero__title">SJ Seguridad Privada LTDA</h1>
                    <p class="sj-dashboard-hero__subtitle">
                        <span x-text="dashboard.scope_label"></span>. Consolida inventario, renovación documental, transferencias y novedades operativas en una sola vista.
                    </p>
                </div>

                <div class="sj-dashboard-hero__meta">
                    <div class="sj-dashboard-stamp">
                        <span class="sj-dashboard-stamp__label">Actualizado</span>
                        <span class="sj-dashboard-stamp__value" x-text="formattedAsOf()"></span>
                    </div>

                    <div class="sj-dashboard-meta">
                        <template x-for="meta in dashboard.meta" :key="meta.label">
                            <div class="sj-metric-chip">
                                <span class="sj-metric-chip__label" x-text="meta.label"></span>
                                <span class="sj-metric-chip__value" x-text="formatNumber(meta.value)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <section class="sj-dashboard-kpis">
                <template x-for="kpi in dashboard.kpis" :key="kpi.label">
                    <article class="sj-kpi-card" :class="`sj-kpi-card--${kpi.tone}`">
                        <div class="sj-kpi-card__label" x-text="kpi.label"></div>
                        <div class="sj-kpi-card__value" x-text="formatNumber(kpi.value)"></div>
                        <div class="sj-kpi-card__helper" x-text="kpi.helper"></div>
                    </article>
                </template>
            </section>

            <section class="sj-dashboard-grid sj-dashboard-grid--primary">
                <article class="sj-panel">
                    <div class="sj-panel__head">
                        <div>
                            <div class="sj-form-section__title">Responsables</div>
                            <h2 class="sj-panel__title">Cantidad de armas por responsable</h2>
                        </div>
                    </div>

                    <template x-if="dashboard.responsible_chart.items.length">
                        <div class="sj-bar-list">
                            <template x-for="item in dashboard.responsible_chart.items" :key="item.label">
                                <div class="sj-bar-list__row">
                                    <div class="sj-bar-list__top">
                                        <span class="sj-bar-list__label" x-text="item.label"></span>
                                        <span class="sj-bar-list__value" x-text="formatNumber(item.value)"></span>
                                    </div>
                                    <div class="sj-bar-list__track">
                                        <div class="sj-bar-list__fill" :style="`width: ${barWidth(item.value, dashboard.responsible_chart.max)}%`"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!dashboard.responsible_chart.items.length">
                        <p class="sj-panel__empty">No hay responsables con armas activas dentro del alcance visible.</p>
                    </template>
                </article>

                <article class="sj-panel">
                    <div class="sj-panel__head">
                        <div>
                            <div class="sj-form-section__title">Riesgo documental</div>
                            <h2 class="sj-panel__title">Estado general de renovación</h2>
                        </div>
                    </div>

                    <div class="sj-donut-card">
                        <div class="sj-donut-wrap">
                            <div class="sj-donut" :style="dashboard.risk_chart.donut_style">
                                <div class="sj-donut__center">
                                    <span class="sj-donut__total" x-text="formatNumber(dashboard.risk_chart.total)"></span>
                                    <span class="sj-donut__caption">armas</span>
                                </div>
                            </div>
                        </div>

                        <div class="sj-legend">
                            <template x-for="item in dashboard.risk_chart.items" :key="item.label">
                                <div class="sj-legend__item">
                                    <span class="sj-legend__swatch" :style="`background: ${item.color}`"></span>
                                    <div class="sj-legend__text">
                                        <span class="sj-legend__label" x-text="item.label"></span>
                                        <span class="sj-legend__value" x-text="formatNumber(item.value)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </article>
            </section>

            <section class="sj-dashboard-grid sj-dashboard-grid--secondary">
                <article class="sj-panel">
                    <div class="sj-panel__head">
                        <div>
                            <div class="sj-form-section__title">Planeación</div>
                            <h2 class="sj-panel__title">Renovaciones por mes</h2>
                        </div>
                    </div>

                    <div class="sj-column-chart">
                        <template x-for="item in dashboard.renewal_chart.items" :key="item.label">
                            <div class="sj-column-chart__item" :class="item.value === 0 ? 'sj-column-chart__item--empty' : ''">
                                <div class="sj-column-chart__value" x-text="formatNumber(item.value)"></div>
                                <div class="sj-column-chart__track">
                                    <template x-if="item.value > 0">
                                        <div class="sj-column-chart__bar" :style="`height: ${columnHeight(item.value, dashboard.renewal_chart.max)}%`"></div>
                                    </template>
                                </div>
                                <div class="sj-column-chart__label" x-text="item.label"></div>
                            </div>
                        </template>
                    </div>
                </article>

                <article class="sj-panel">
                    <div class="sj-panel__head">
                        <div>
                            <div class="sj-form-section__title">Novedades</div>
                            <h2 class="sj-panel__title">Incidentes activos por observación</h2>
                        </div>
                    </div>

                    <template x-if="dashboard.incident_chart.total > 0">
                        <div class="sj-bar-list sj-bar-list--compact">
                            <template x-for="item in dashboard.incident_chart.items.filter((entry) => entry.value > 0)" :key="item.label">
                                <div class="sj-bar-list__row">
                                    <div class="sj-bar-list__top">
                                        <span class="sj-bar-list__label" x-text="item.label"></span>
                                        <span class="sj-bar-list__value" x-text="formatNumber(item.value)"></span>
                                    </div>
                                    <div class="sj-bar-list__track sj-bar-list__track--soft">
                                        <div class="sj-bar-list__fill sj-bar-list__fill--custom" :style="`width: ${barWidth(item.value, dashboard.incident_chart.max, 8)}%; background: ${item.color}`"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="dashboard.incident_chart.total === 0">
                        <p class="sj-panel__empty">No hay novedades activas registradas en documentos manuales.</p>
                    </template>
                </article>
            </section>

            <section class="sj-dashboard-grid sj-dashboard-grid--tertiary">
                <article class="sj-panel">
                    <div class="sj-panel__head">
                        <div>
                            <div class="sj-form-section__title">Transferencias</div>
                            <h2 class="sj-panel__title">Estado de solicitudes</h2>
                        </div>
                    </div>

                    <div class="sj-stat-list">
                        <template x-for="item in dashboard.transfer_chart.items" :key="item.label">
                            <div class="sj-stat-list__item">
                                <div class="sj-stat-list__meta">
                                    <span class="sj-stat-list__dot" :style="`background: ${item.color}`"></span>
                                    <span class="sj-stat-list__label" x-text="item.label"></span>
                                </div>
                                <span class="sj-stat-list__value" x-text="formatNumber(item.value)"></span>
                            </div>
                        </template>
                    </div>
                </article>

                <article class="sj-panel">
                    <div class="sj-panel__head">
                        <div>
                            <div class="sj-form-section__title">Distribución</div>
                            <h2 class="sj-panel__title">Ubicación operativa interna</h2>
                        </div>
                    </div>

                    <div class="sj-stat-list">
                        <template x-for="item in dashboard.operational_chart.items" :key="item.label">
                            <div class="sj-stat-list__item">
                                <div class="sj-stat-list__meta">
                                    <span class="sj-stat-list__dot" :style="`background: ${item.color}`"></span>
                                    <span class="sj-stat-list__label" x-text="item.label"></span>
                                </div>
                                <span class="sj-stat-list__value" x-text="formatNumber(item.value)"></span>
                            </div>
                        </template>
                    </div>
                </article>
            </section>
        </div>
    </div>
</x-app-layout>