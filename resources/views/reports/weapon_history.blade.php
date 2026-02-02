<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Historial por arma') }}
            </h2>
            <a href="{{ route('reports.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Volver') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <form method="GET" class="flex items-center gap-2">
                <label class="text-sm text-gray-600">{{ __('Arma') }}</label>
                <select name="weapon_id" class="rounded-md border-gray-300 text-sm">
                    <option value="">{{ __('Seleccione') }}</option>
                    @foreach ($weapons as $weaponItem)
                        <option value="{{ $weaponItem->id }}" @selected($weapon?->id === $weaponItem->id)>
                            {{ $weaponItem->internal_code }} - {{ $weaponItem->serial_number }}
                        </option>
                    @endforeach
                </select>
                <button class="text-xs text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded">
                    {{ __('Ver historial') }}
                </button>
            </form>

            @if ($weapon)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="font-semibold">{{ $weapon->internal_code }} - {{ $weapon->serial_number }}</div>
                        <div class="text-sm text-gray-500">{{ $weapon->weapon_type }} / {{ $weapon->brand }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-3">{{ __('Asignaciones a cliente') }}</h3>
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Responsable') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Cliente') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Inicio') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Fin') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($assignments as $assignment)
                                    <tr>
                                        <td class="px-3 py-2">{{ $assignment->responsible?->name ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $assignment->client?->name }}</td>
                                        <td class="px-3 py-2">{{ $assignment->start_at?->format('Y-m-d') }}</td>
                                        <td class="px-3 py-2">{{ $assignment->end_at?->format('Y-m-d') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-gray-500">
                                            {{ __('Sin asignaciones registradas.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-3">{{ __('Documentos') }}</h3>
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Documento') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Fecha') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Observaciones') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($documents as $document)
                                    <tr>
                                        <td class="px-3 py-2">{{ $document->file?->original_name ?? __('Documento') }}
                                        <div class="text-xs text-gray-500">{{ $document->file?->mime_type ?? '-' }}</div></td>
                                        <td class="px-3 py-2">{{ $document->valid_until?->format('Y-m-d') ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $document->observations ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-6 text-center text-gray-500">
                                            {{ __('Sin documentos registrados.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
