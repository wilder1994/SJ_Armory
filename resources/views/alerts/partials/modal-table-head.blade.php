@props(['targetBody'])

@php
    $alertTableColumns = [
        ['key' => 'cliente', 'label' => __('alerts.columns.cliente')],
        ['key' => 'tipo', 'label' => __('alerts.columns.tipo')],
        ['key' => 'serie', 'label' => __('alerts.columns.serie')],
        ['key' => 'vence', 'label' => __('alerts.columns.vence')],
        ['key' => 'estado', 'label' => __('alerts.columns.estado')],
        ['key' => 'observacion', 'label' => __('alerts.columns.observacion')],
    ];
@endphp

<thead class="alerts-table-head">
    <tr>
        @foreach ($alertTableColumns as $column)
            <th class="alerts-col-filter-th" scope="col">
                <div class="alerts-col-filter">
                    <span class="alerts-col-filter__label">{{ $column['label'] }}</span>
                    <button
                        type="button"
                        class="alerts-col-filter__trigger"
                        data-col-filter-trigger
                        data-col-filter="{{ $column['key'] }}"
                        data-target-body="{{ $targetBody }}"
                        aria-expanded="false"
                        aria-haspopup="dialog"
                        aria-controls="alerts-column-filter-popover"
                        aria-label="{{ __('alerts.filter_column', ['column' => $column['label']]) }}"
                    >
                        <svg class="alerts-col-filter__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.24 4.5a.75.75 0 01-1.08 0l-4.24-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </th>
        @endforeach
    </tr>
</thead>
