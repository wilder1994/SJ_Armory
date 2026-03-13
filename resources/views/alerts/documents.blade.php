<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Alertas documentales') }}
            </h2>
            <a href="{{ route('reports.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Volver') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" class="flex items-center gap-2">
                <label class="text-sm text-gray-600">{{ __('Ventana') }}</label>
                <select name="days" class="rounded-md border-gray-300 text-sm">
                    <option value="30" @selected($days === 30)>{{ __('30 días') }}</option>
                    <option value="60" @selected($days === 60)>{{ __('60 días') }}</option>
                    <option value="90" @selected($days === 90)>{{ __('90 días') }}</option>
                    <option value="120" @selected($days === 120)>{{ __('120 días') }}</option>
                </select>
                <button class="text-xs text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded">
                    {{ __('Filtrar') }}
                </button>
            </form>

            <form method="POST" action="{{ route('alerts.documents.download') }}" class="space-y-6">
                @csrf

                <div class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
                    <div class="text-sm text-gray-600">
                        {{ __('Seleccione una o varias armas para generar un solo documento de revalidación.') }}
                    </div>
                    <x-primary-button>
                        {{ __('Descargar relación') }}
                    </x-primary-button>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-3">{{ __('Documentos vencidos') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">
                                            <span class="sr-only">{{ __('Seleccionar') }}</span>
                                        </th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Arma') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Documento') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Estado') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Observación') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Venció') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($expired as $doc)
                                        @php($alert = \App\Support\WeaponDocumentAlert::forComplianceDocument($doc))
                                        <tr class="{{ $alert['row_class'] }}">
                                            <td class="px-3 py-2">
                                                <input type="checkbox" name="weapon_ids[]" value="{{ $doc->weapon_id }}" class="rounded border-gray-300 text-indigo-600">
                                            </td>
                                            <td class="px-3 py-2">{{ $doc->weapon?->internal_code }}</td>
                                            <td class="px-3 py-2">
                                                {{ $doc->document_name ?? __('Documento') }}
                                                <div class="text-xs text-gray-500">{{ $doc->weapon?->weapon_type }} / {{ $doc->weapon?->serial_number }}</div>
                                            </td>
                                            <td class="px-3 py-2 {{ $alert['text_class'] }}">{{ $alert['state'] }}</td>
                                            <td class="px-3 py-2 {{ $alert['text_class'] }}">{{ $alert['observation'] }}</td>
                                            <td class="px-3 py-2">{{ $doc->valid_until?->format('Y-m-d') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-6 text-center text-gray-500">
                                                {{ __('Sin documentos vencidos.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-3">{{ __('Documentos por vencer') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">
                                            <span class="sr-only">{{ __('Seleccionar') }}</span>
                                        </th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Arma') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Documento') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Estado') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Observación') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Vence') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($expiring as $doc)
                                        @php($alert = \App\Support\WeaponDocumentAlert::forComplianceDocument($doc))
                                        <tr class="{{ $alert['row_class'] }}">
                                            <td class="px-3 py-2">
                                                <input type="checkbox" name="weapon_ids[]" value="{{ $doc->weapon_id }}" class="rounded border-gray-300 text-indigo-600">
                                            </td>
                                            <td class="px-3 py-2">{{ $doc->weapon?->internal_code }}</td>
                                            <td class="px-3 py-2">
                                                {{ $doc->document_name ?? __('Documento') }}
                                                <div class="text-xs text-gray-500">{{ $doc->weapon?->weapon_type }} / {{ $doc->weapon?->serial_number }}</div>
                                            </td>
                                            <td class="px-3 py-2 {{ $alert['text_class'] }}">{{ $alert['state'] }}</td>
                                            <td class="px-3 py-2 {{ $alert['text_class'] }}">{{ $alert['observation'] }}</td>
                                            <td class="px-3 py-2">{{ $doc->valid_until?->format('Y-m-d') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-6 text-center text-gray-500">
                                                {{ __('Sin documentos por vencer.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
