<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <p class="sj-section-header__eyebrow">{{ __('Centro de reportes') }}</p>
                <h2 class="sj-section-header__title">{{ __('Custodia y taller') }}</h2>
                <p class="sj-section-header__subtitle">
                    {{ __('Armas ubicadas en armerillo, pendientes de mantenimiento o en armero. No son novedades operativas.') }}
                </p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('reports.index') }}" class="sj-ui-btn sj-ui-btn--ghost">
                    {{ __('Volver a reportes') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach ($roleLabels as $role => $label)
                    <div class="sj-ui-kpi sj-ui-kpi--blue">
                        <span class="sj-ui-kpi__label">{{ $label }}</span>
                        <div class="sj-ui-kpi__row">
                            <span class="sj-ui-kpi__value">{{ $counts[$role] ?? 0 }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="sj-ui-card overflow-hidden">
                <div class="sj-table-wrap overflow-x-auto">
                <table class="sj-table min-w-full text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Estado') }}</th>
                            <th>{{ __('Puesto') }}</th>
                            <th>{{ __('Serie') }}</th>
                            <th>{{ __('Cliente') }}</th>
                            <th>{{ __('Responsable') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-3 py-2">{{ $row['custody_label'] }}</td>
                                <td class="px-3 py-2">{{ $row['post_name'] }}</td>
                                <td class="px-3 py-2 font-medium">{{ $row['weapon']->serial_number ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $row['client_name'] }}</td>
                                <td class="px-3 py-2">{{ $row['responsible_name'] }}</td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ route('weapons.show', $row['weapon']) }}" class="font-semibold text-[#0b6fb6]">{{ __('Ver arma') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-slate-500">
                                    {{ __('No hay armas en puestos de custodia o taller en su alcance.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
