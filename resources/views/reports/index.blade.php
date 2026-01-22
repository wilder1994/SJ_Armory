<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reportes') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <a href="{{ route('reports.assignments') }}" class="rounded border border-gray-200 p-4 hover:border-indigo-200">
                            <div class="text-sm font-semibold">{{ __('Armas por cliente') }}</div>
                            <div class="text-xs text-gray-500">{{ __('Destino activo') }}</div>
                        </a>
                        <a href="{{ route('reports.no_destination') }}" class="rounded border border-gray-200 p-4 hover:border-indigo-200">
                            <div class="text-sm font-semibold">{{ __('Armas sin destino') }}</div>
                            <div class="text-xs text-gray-500">{{ __('Sin asignación activa') }}</div>
                        </a>
                        <a href="{{ route('reports.history') }}" class="rounded border border-gray-200 p-4 hover:border-indigo-200">
                            <div class="text-sm font-semibold">{{ __('Historial por arma') }}</div>
                            <div class="text-xs text-gray-500">{{ __('Destino + documentos') }}</div>
                        </a>
                        <a href="{{ route('reports.audit') }}" class="rounded border border-gray-200 p-4 hover:border-indigo-200">
                            <div class="text-sm font-semibold">{{ __('Auditoría reciente') }}</div>
                            <div class="text-xs text-gray-500">{{ __('Últimos 30/90 días') }}</div>
                        </a>
                        <a href="{{ route('alerts.documents') }}" class="rounded border border-gray-200 p-4 hover:border-indigo-200">
                            <div class="text-sm font-semibold">{{ __('Alertas documentales') }}</div>
                            <div class="text-xs text-gray-500">{{ __('Vencimientos y revalidaciones') }}</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
