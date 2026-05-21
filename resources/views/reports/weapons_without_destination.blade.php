<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Armas sin destino') }}
            </h2>
            <a href="{{ route('reports.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Volver') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto sj-table-wrap">
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




