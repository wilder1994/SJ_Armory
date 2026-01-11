<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ Auth::user()->isResponsible() && !Auth::user()->isAdmin() ? __('Mis clientes') : __('Clientes') }}
            </h2>
            @can('create', App\Models\Client::class)
                <a href="{{ route('clients.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                    {{ __('Nuevo cliente') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Nombre') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Contacto') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Teléfono') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Email') }}</th>
                                <th class="px-3 py-2 text-right font-medium text-gray-600">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($clients as $client)
                                <tr>
                                    <td class="px-3 py-2">{{ $client->name }}</td>
                                    <td class="px-3 py-2">{{ $client->contact_name }}</td>
                                    <td class="px-3 py-2">{{ $client->phone }}</td>
                                    <td class="px-3 py-2">{{ $client->email }}</td>
                                    <td class="px-3 py-2 text-right space-x-2">
                                        @can('update', $client)
                                            <a href="{{ route('clients.edit', $client) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ __('Editar') }}
                                            </a>
                                        @endcan
                                        @can('delete', $client)
                                            <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Eliminar cliente?')">
                                                    {{ __('Eliminar') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                                        {{ __('No hay clientes registrados.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $clients->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
