<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ Auth::user()->isResponsible() && !Auth::user()->isAdmin() ? __('Mis armas') : __('Armamento') }}
            </h2>
            <div class="flex-1 flex justify-center">
                <div class="w-full max-w-md">
                    <input id="weapons-search" type="search" name="q" value="{{ $search ?? '' }}"
                        class="w-full rounded-md border-gray-300 text-sm"
                        placeholder="{{ __('Buscar en todas las columnas...') }}">
                </div>
            </div>
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
                    <div id="weapons-table-scroll" class="w-full overflow-auto weapons-table-scroll relative" style="max-height: calc(100vh - 320px);">
                            <table class="min-w-full divide-y divide-gray-200 text-sm min-w-[2200px]">
                        <thead class="bg-gray-50 sticky top-0 z-20">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap bg-gray-50">{{ __('Código interno') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 min-w-[200px] whitespace-nowrap bg-gray-50">{{ __('Cliente') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 min-w-[200px] whitespace-nowrap bg-gray-50">{{ __('Responsable') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap bg-gray-50">{{ __('Serie') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap bg-gray-50">{{ __('Tipo de arma') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap bg-gray-50">{{ __('Tipo de permiso') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap bg-gray-50">{{ __('Número de permiso') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap bg-gray-50">{{ __('Vence') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap bg-gray-50">{{ __('Estado') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 min-w-[220px] whitespace-nowrap bg-gray-50">{{ __('Puesto o trabajador') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap bg-gray-50">{{ __('Cédula') }}</th>
                                <th class="px-3 py-2 text-right font-medium text-gray-600 whitespace-nowrap bg-gray-50">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody id="weapons-tbody" class="divide-y divide-gray-200">
                            @include('weapons.partials.index_rows', ['weapons' => $weapons])
                        </tbody>
                        </table>
                    </div>

                    <div id="weapons-pagination">
                        @include('weapons.partials.index_pagination', ['weapons' => $weapons])
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
        const input = document.getElementById('weapons-search');
        const tbody = document.getElementById('weapons-tbody');
        const pagination = document.getElementById('weapons-pagination');
        if (!input || !tbody || !pagination) {
            return;
        }

        const highlight = (term) => {
            if (!term) {
                tbody.querySelectorAll('td').forEach((cell) => {
                    if (cell.dataset.original !== undefined) {
                        cell.innerHTML = cell.dataset.original;
                    }
                });
                return;
            }

            const escaped = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(escaped, 'gi');

            tbody.querySelectorAll('td').forEach((cell) => {
                if (cell.dataset.original === undefined) {
                    cell.dataset.original = cell.innerHTML;
                }
                const text = cell.dataset.original;
                cell.innerHTML = text.replace(regex, (match) => `<mark class="bg-yellow-200">${match}</mark>`);
            });
        };

        const updateList = async (url) => {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) {
                return;
            }
            const data = await response.json();
            tbody.innerHTML = data.tbody;
            pagination.innerHTML = data.pagination;
            highlight(input.value.trim());
        };

        let timer = null;
        input.addEventListener('input', () => {
            const query = input.value.trim();
            const url = new URL(window.location.href);
            url.searchParams.set('q', query);
            url.searchParams.set('page', '1');
            window.history.replaceState({}, '', url.toString());

            if (timer) {
                clearTimeout(timer);
            }

            timer = setTimeout(() => {
                updateList(url.toString());
            }, 300);
        });

        pagination.addEventListener('click', (event) => {
            const link = event.target.closest('a');
            if (!link) return;
            event.preventDefault();
            const url = new URL(link.href);
            const query = input.value.trim();
            if (query) {
                url.searchParams.set('q', query);
            }
            window.history.replaceState({}, '', url.toString());
            updateList(url.toString());
        });

        if (input.value.trim() !== '') {
            highlight(input.value.trim());
        }
    })();
</script>


