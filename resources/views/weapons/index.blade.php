<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ Auth::user()->isResponsible() && !Auth::user()->isAdmin() ? __('Mis armas') : __('Armamento') }}
            </h2>
            @can('create', App\Models\Weapon::class)
                <a href="{{ route('weapons.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                    {{ __('Nueva arma') }}
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
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Código interno') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Serie') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Tipo') }}</th>
                                @if (Auth::user()->isResponsible() && !Auth::user()->isAdmin())
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Cliente') }}</th>
                                @endif
                                <th class="px-3 py-2 text-right font-medium text-gray-600">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($weapons as $weapon)
                                <tr>
                                    <td class="px-3 py-2">{{ $weapon->internal_code }}</td>
                                    <td class="px-3 py-2">{{ $weapon->serial_number }}</td>
                                    <td class="px-3 py-2">{{ $weapon->weapon_type }}</td>
                                    @if (Auth::user()->isResponsible() && !Auth::user()->isAdmin())
                                        <td class="px-3 py-2">{{ $weapon->activeClientAssignment?->client?->name ?? __('Sin destino') }}</td>
                                    @endif
                                    <td class="px-3 py-2 text-right space-x-2">
                                        <a href="{{ route('weapons.show', $weapon) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ __('Ver') }}
                                        </a>
                                        @can('update', $weapon)
                                            <a href="{{ route('weapons.edit', $weapon) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ __('Editar') }}
                                            </a>
                                        @endcan
                                        @can('delete', $weapon)
                                            <form action="{{ route('weapons.destroy', $weapon) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Eliminar arma?')">
                                                    {{ __('Eliminar') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->isResponsible() && !Auth::user()->isAdmin() ? 5 : 4 }}" class="px-3 py-6 text-center text-gray-500">
                                        {{ __('No hay armas registradas.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $weapons->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
