<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalle de arma') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('weapons.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    {{ __('Volver') }}
                </a>
                @can('update', $weapon)
                    <a href="{{ route('weapons.edit', $weapon) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                        {{ __('Editar') }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @include('weapons.partials.details')

            @can('assignToClient', $weapon)
                @include('weapons.partials.assignment')
            @endcan

            @if (Auth::user()->isAdmin() || Auth::user()->isResponsible())
                @include('weapons.partials.assignment_internal')
            @endif

            @if (Auth::user()->isAdmin() || Auth::user()->isResponsible())
                @include('weapons.partials.transfer')
            @endif

            @include('weapons.partials.photos')

            @include('weapons.partials.documents')
        </div>
    </div>
</x-app-layout>
