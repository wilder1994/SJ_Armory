<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Auditoría reciente') }}
            </h2>
            <a href="{{ route('reports.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Volver') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" class="mb-4 flex items-center gap-2">
                <label class="text-sm text-gray-600">{{ __('Rango') }}</label>
                <select name="days" class="rounded-md border-gray-300 text-sm">
                    <option value="30" @selected($days === 30)>{{ __('Últimos 30 días') }}</option>
                    <option value="90" @selected($days === 90)>{{ __('Últimos 90 días') }}</option>
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
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Fecha') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Usuario') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Acción') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Entidad') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="px-3 py-2">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2">{{ $log->user?->name ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $log->action }}</td>
                                    <td class="px-3 py-2">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-gray-500">
                                        {{ __('Sin auditoría en el rango.') }}
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
