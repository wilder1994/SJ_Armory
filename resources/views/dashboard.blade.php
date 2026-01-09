<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-sm text-gray-600">{{ __('Bienvenido al Control de Armamento') }}</div>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @can('viewAny', App\Models\Weapon::class)
                            <a href="{{ route('weapons.index') }}" class="rounded border border-gray-200 px-4 py-2 text-sm text-indigo-600 hover:border-indigo-200">
                                {{ __('Ver armamento') }}
                            </a>
                        @endcan
                        @can('viewAny', App\Models\Client::class)
                            <a href="{{ route('clients.index') }}" class="rounded border border-gray-200 px-4 py-2 text-sm text-indigo-600 hover:border-indigo-200">
                                {{ __('Ver clientes') }}
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
