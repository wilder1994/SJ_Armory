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
        <div class="w-full px-4 sm:px-6 lg:px-8 pb-20">
            @if (session('status'))
                <div class="mb-4 rounded bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg w-full">
                <div class="p-6 text-gray-900">
                    <div id="weapons-table-scroll" class="overflow-x-auto w-full weapons-table-scroll">
                        <table class="min-w-full divide-y divide-gray-200 text-sm min-w-[2200px]">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap">{{ __('Código interno') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 min-w-[200px] whitespace-nowrap">{{ __('Cliente') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 min-w-[200px] whitespace-nowrap">{{ __('Responsable') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap">{{ __('Serie') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap">{{ __('Tipo de arma') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap">{{ __('Tipo de permiso') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap">{{ __('Número de permiso') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap">{{ __('Vence') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap">{{ __('Estado') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 min-w-[220px] whitespace-nowrap">{{ __('Puesto o trabajador') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap">{{ __('Cédula') }}</th>
                                <th class="px-3 py-2 text-right font-medium text-gray-600 whitespace-nowrap">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($weapons as $weapon)
                                <tr>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <span title="{{ $weapon->internal_code }}">
                                            {{ \Illuminate\Support\Str::limit($weapon->internal_code, 8) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 min-w-[200px] whitespace-nowrap">{{ $weapon->activeClientAssignment?->client?->name ?? __('Sin destino') }}</td>
                                    <td class="px-3 py-2 min-w-[200px] whitespace-nowrap">{{ $weapon->activeClientAssignment?->responsible?->name ?? '-' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $weapon->serial_number }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $weapon->weapon_type }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $weapon->permit_type ?? '-' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $weapon->permit_number ?? '-' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $weapon->permit_expires_at?->format('Y-m-d') ?? '-' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        {{ $weapon->activeClientAssignment ? __('Asignada') : __('Sin destino') }}
                                    </td>
                                    <td class="px-3 py-2 min-w-[220px] whitespace-nowrap">
                                        @if ($weapon->activePostAssignment)
                                            {{ $weapon->activePostAssignment->post?->name }}
                                        @elseif ($weapon->activeWorkerAssignment)
                                            {{ $weapon->activeWorkerAssignment->worker?->name }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        {{ $weapon->activeWorkerAssignment?->worker?->document ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-right space-x-2 whitespace-nowrap">
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
                                    <td colspan="12" class="px-3 py-6 text-center text-gray-500">
                                        {{ __('No hay armas registradas.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $weapons->links() }}
                    </div>
                </div>
            </div>

            <div id="weapons-scrollbar-shell" class="fixed bottom-0 left-0 right-0 z-50 px-4 pb-0.5 sm:px-6 lg:px-8">
                <div id="weapons-scrollbar" class="overflow-x-auto w-full">
                    <div class="min-w-[2200px] h-3"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    #weapons-table-scroll {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    #weapons-table-scroll::-webkit-scrollbar {
        height: 0;
    }
</style>

<script>
    (() => {
        const tableScroll = document.getElementById('weapons-table-scroll');
        const barScroll = document.getElementById('weapons-scrollbar');
        const barShell = document.getElementById('weapons-scrollbar-shell');
        if (!tableScroll || !barScroll || !barShell) {
            return;
        }

        let syncing = false;

        const sync = (from, to) => {
            if (syncing) return;
            syncing = true;
            to.scrollLeft = from.scrollLeft;
            syncing = false;
        };

        barScroll.addEventListener('scroll', () => sync(barScroll, tableScroll));
        tableScroll.addEventListener('scroll', () => sync(tableScroll, barScroll));
    })();
</script>

<script>
    (() => {
        const tableScroll = document.getElementById('weapons-table-scroll');
        const barScroll = document.getElementById('weapons-scrollbar');
        if (!tableScroll || !barScroll) {
            return;
        }

        let syncing = false;

        const sync = (from, to) => {
            if (syncing) return;
            syncing = true;
            to.scrollLeft = from.scrollLeft;
            syncing = false;
        };

        barScroll.addEventListener('scroll', () => sync(barScroll, tableScroll));
        tableScroll.addEventListener('scroll', () => sync(tableScroll, barScroll));
    })();
</script>


