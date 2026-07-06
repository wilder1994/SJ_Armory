<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Armas por cliente') }}</h2>
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
                            <div class="sj-ui-field min-w-[12rem] flex-1">
                                <label for="weapons-by-client-filter" class="sj-ui-field__label">{{ __('Cliente') }}</label>
                                <select id="weapons-by-client-filter" name="client_id" class="sj-ui-field__control">
                                    <option value="">{{ __('Todos') }}</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" @selected($clientId === $client->id)>
                                            {{ $client->name }}
                                        </option>
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
                                <th>{{ __('Código') }}</th>
                                <th>{{ __('Serie') }}</th>
                                <th>{{ __('Responsable') }}</th>
                                <th>{{ __('Cliente') }}</th>
                                <th>{{ __('Desde') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($weapons as $weapon)
                                <tr>
                                    <td class="px-3 py-2">{{ $weapon->internal_code }}</td>
                                    <td class="px-3 py-2">{{ $weapon->serial_number }}</td>
                                    <td class="px-3 py-2">{{ $weapon->activeClientAssignment?->responsible?->name ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->activeClientAssignment?->client?->name ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->activeClientAssignment?->start_at?->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                                        {{ __('Sin resultados.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                    <div class="mt-4">
                        {{ $weapons->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>




