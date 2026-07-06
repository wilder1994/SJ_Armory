<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">
                    {{ __('Auditoría reciente') }}
                </h2>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('reports.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide">
            <div class="sj-ui-card overflow-hidden">
                <div class="sj-ui-card__body p-6">
                    <form method="GET" class="sj-ui-filter-bar">
                        <div class="sj-ui-filter-bar__fields">
                            <div class="sj-ui-field w-44 shrink-0">
                                <label for="audit-filter-days" class="sj-ui-field__label">{{ __('Rango') }}</label>
                                <select id="audit-filter-days" name="days" class="sj-ui-field__control">
                                    <option value="30" @selected($days === 30)>{{ __('Últimos 30 días') }}</option>
                                    <option value="90" @selected($days === 90)>{{ __('Últimos 90 días') }}</option>
                                </select>
                            </div>
                            <div class="sj-ui-field min-w-[10rem] flex-1">
                                <label for="audit-filter-module" class="sj-ui-field__label">{{ __('Módulo') }}</label>
                                <select id="audit-filter-module" name="module" class="sj-ui-field__control">
                                    @foreach ($modules as $value => $label)
                                        <option value="{{ $value }}" @selected($module === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sj-ui-filter-bar__actions">
                                <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Filtrar') }}</button>
                            </div>
                        </div>
                    </form>

                    <div class="sj-table-wrap overflow-x-auto">
                    <table class="sj-table sj-table--align-left min-w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Fecha') }}</th>
                                <th>{{ __('Usuario') }}</th>
                                <th>{{ __('Acción') }}</th>
                                <th>{{ __('Entidad') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="px-3 py-2">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2">{{ $log->user?->name ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        {{ $actionLabels[$log->action] ?? ucfirst(str_replace('_', ' ', $log->action)) }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $entityLabel = $entityLabels[$log->auditable_type] ?? class_basename($log->auditable_type);
                                            $entityName = null;
                                            if ($log->auditable) {
                                                if ($log->auditable instanceof \App\Models\User) {
                                                    $entityName = $log->auditable->name;
                                                } elseif ($log->auditable instanceof \App\Models\Client) {
                                                    $entityName = $log->auditable->name;
                                                } elseif ($log->auditable instanceof \App\Models\Weapon) {
                                                    $entityName = $log->auditable->internal_code ?? ('#' . $log->auditable->id);
                                                } elseif ($log->auditable instanceof \App\Models\WeaponClientAssignment) {
                                                    $entityName = $log->auditable->client?->name;
                                                } elseif ($log->auditable instanceof \App\Models\WeaponTransfer) {
                                                    $transferWeapon = $log->auditable->loadMissing('weapon', 'fromUser', 'toUser');
                                                    $weaponCode = $transferWeapon->weapon?->internal_code ?? ('#' . $transferWeapon->weapon_id);
                                                    $fromName = $transferWeapon->fromUser?->name;
                                                    $toName = $transferWeapon->toUser?->name;
                                                    $transferDate = $log->auditable->requested_at?->format('Y-m-d H:i');
                                                    $entityName = __('Arma') . ' ' . $weaponCode;
                                                    if ($fromName || $toName) {
                                                        $entityName .= ' (' . ($fromName ?? '-') . ' -> ' . ($toName ?? '-') . ')';
                                                    }
                                                    if ($transferDate) {
                                                        $entityName .= ' - ' . $transferDate;
                                                    }
                                                } elseif ($log->auditable instanceof \App\Models\Post) {
                                                    $entityName = $log->auditable->name;
                                                } elseif ($log->auditable instanceof \App\Models\Worker) {
                                                    $entityName = $log->auditable->name;
                                                }
                                            }
                                        @endphp
                                        {{ $entityLabel }}
                                        @if ($entityName)
                                            - {{ $entityName }}
                                        @else
                                            #{{ $log->auditable_id }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-gray-500">
                                        {{ __('Sin auditoría en el rango.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
