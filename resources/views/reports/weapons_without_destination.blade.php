<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Armas sin destino') }}</h2>
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
                    <div class="sj-table-wrap overflow-x-auto">
                    <table class="sj-table sj-table--align-left min-w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Código') }}</th>
                                <th>{{ __('Serie') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($weapons as $weapon)
                                <tr>
                                    <td class="px-3 py-2">{{ $weapon->internal_code }}</td>
                                    <td class="px-3 py-2">{{ $weapon->serial_number }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-3 py-6 text-center text-gray-500">
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
