<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transferencias') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('transfers.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <label class="text-sm text-gray-600">{{ __('Buscar') }}</label>
                            <input type="text" name="q" value="{{ $search }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm" placeholder="{{ __('Código o cliente') }}">
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">{{ __('Estado') }}</label>
                            <select name="status" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                <option value="pending" @selected($status === 'pending')>{{ __('Pendiente') }}</option>
                                <option value="accepted" @selected($status === 'accepted')>{{ __('Aceptada') }}</option>
                                <option value="rejected" @selected($status === 'rejected')>{{ __('Rechazada') }}</option>
                                <option value="cancelled" @selected($status === 'cancelled')>{{ __('Cancelada') }}</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="text-sm text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-2 rounded">
                                {{ __('Filtrar') }}
                            </button>
                            <a href="{{ route('transfers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('Limpiar') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @php
                $statusLabels = [
                    'pending' => __('Pendientes'),
                    'accepted' => __('Aceptadas'),
                    'rejected' => __('Rechazadas'),
                    'cancelled' => __('Canceladas'),
                ];
                $statusLabel = $statusLabels[$status] ?? __('Pendientes');
            @endphp

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold">
                        {{ $status === 'pending' ? __('Pendientes por aceptar') : __('Recibidas') . ' - ' . $statusLabel }}
                    </h3>
                    <table class="mt-3 min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Arma') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Cliente') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Remitente') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Nuevo cliente') }}</th>
                                <th class="px-3 py-2 text-right font-medium text-gray-600">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($incoming as $transfer)
                                <tr>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('weapons.show', $transfer->weapon) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $transfer->weapon?->internal_code ?? $transfer->weapon_id }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-2">{{ $transfer->weapon?->activeClientAssignment?->client?->name }}</td>
                                    <td class="px-3 py-2">{{ $transfer->fromUser?->name }}</td>
                                    <td class="px-3 py-2">{{ $transfer->newClient?->name ?? '-' }}</td>
                                    <td class="px-3 py-2 text-right space-x-2">
                                        @if ($status === 'pending')
                                            <form action="{{ route('transfers.accept', $transfer) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-emerald-600 hover:text-emerald-900">
                                                    {{ __('Aceptar') }}
                                                </button>
                                            </form>
                                            <form action="{{ route('transfers.reject', $transfer) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    {{ __('Rechazar') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-500">{{ __('Sin acciones') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                                        {{ __('No hay transferencias para este filtro.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold">{{ __('Transferencias enviadas') }} - {{ $statusLabel }}</h3>
                    <table class="mt-3 min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Arma') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Cliente') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Destinatario') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Nuevo cliente') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Fecha') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($outgoing as $transfer)
                                <tr>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('weapons.show', $transfer->weapon) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $transfer->weapon?->internal_code ?? $transfer->weapon_id }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-2">{{ $transfer->weapon?->activeClientAssignment?->client?->name }}</td>
                                    <td class="px-3 py-2">{{ $transfer->toUser?->name }}</td>
                                    <td class="px-3 py-2">{{ $transfer->newClient?->name ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $transfer->requested_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                                        {{ __('No hay transferencias para este filtro.') }}
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
