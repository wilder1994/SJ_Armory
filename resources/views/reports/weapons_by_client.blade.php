<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Armas por cliente') }}
            </h2>
            <a href="{{ route('reports.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Volver') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" class="mb-4 flex items-center gap-2">
                <label class="text-sm text-gray-600">{{ __('Cliente') }}</label>
                <select name="client_id" class="rounded-md border-gray-300 text-sm">
                    <option value="">{{ __('Todos') }}</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected($clientId === $client->id)>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
                <button class="text-xs text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded">
                    {{ __('Filtrar') }}
                </button>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Código') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Serie') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Cliente') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Desde') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($weapons as $weapon)
                                <tr>
                                    <td class="px-3 py-2">{{ $weapon->internal_code }}</td>
                                    <td class="px-3 py-2">{{ $weapon->serial_number }}</td>
                                    <td class="px-3 py-2">{{ $weapon->activeClientAssignment?->client?->name ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->activeClientAssignment?->start_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-gray-500">
                                        {{ __('Sin resultados.') }}
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
