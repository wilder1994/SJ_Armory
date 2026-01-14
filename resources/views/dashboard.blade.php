<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">{{ __('Inicio') }}</h1>
                <p class="text-sm text-gray-500">{{ __('Accesos rápidos a los módulos principales.') }}</p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @can('viewAny', App\Models\Weapon::class)
                    <a href="{{ route('weapons.index') }}" class="group rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path d="M3 16h8l3-3h6v4H3z" />
                                    <path d="M6 16v3M10 16v3M16 13V9l3-3h2" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-base font-semibold text-gray-800">{{ __('Armamento') }}</div>
                                <div class="text-xs text-gray-500">{{ __('Gestión de armas') }}</div>
                            </div>
                        </div>
                    </a>
                @endcan

                @can('viewAny', App\Models\Client::class)
                    <a href="{{ route('clients.index') }}" class="group rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <circle cx="9" cy="8" r="3" />
                                    <path d="M3 20a6 6 0 0112 0" />
                                    <circle cx="17" cy="7" r="2" />
                                    <path d="M17 9a5 5 0 014 4" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-base font-semibold text-gray-800">{{ __('Clientes') }}</div>
                                <div class="text-xs text-gray-500">{{ __('Cartera y contactos') }}</div>
                            </div>
                        </div>
                    </a>
                @endcan

                @if (Auth::user()?->isAdmin())
                    <a href="{{ route('portfolios.index') }}" class="group rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path d="M4 7h16v10H4z" />
                                    <path d="M8 7V5h8v2" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-base font-semibold text-gray-800">{{ __('Carteras') }}</div>
                                <div class="text-xs text-gray-500">{{ __('Asignación de clientes') }}</div>
                            </div>
                        </div>
                    </a>
                @endif

                @if (Auth::user()?->isAdmin())
                    <a href="{{ route('reports.index') }}" class="group rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-sky-50 text-sky-600">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path d="M4 19h16" />
                                    <path d="M6 16V8" />
                                    <path d="M12 16V5" />
                                    <path d="M18 16v-6" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-base font-semibold text-gray-800">{{ __('Reportes') }}</div>
                                <div class="text-xs text-gray-500">{{ __('Consultas y auditoría') }}</div>
                            </div>
                        </div>
                    </a>
                @endif

                @if (Auth::user()?->isAdmin())
                    <a href="{{ route('alerts.documents') }}" class="group rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path d="M12 3v10" />
                                    <path d="M12 17h.01" />
                                    <path d="M4.5 20h15L12 4 4.5 20z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-base font-semibold text-gray-800">{{ __('Alertas') }}</div>
                                <div class="text-xs text-gray-500">{{ __('Vencimientos y revalidaciones') }}</div>
                            </div>
                        </div>
                    </a>
                @endif

                @if (Auth::user()?->isAdmin())
                    <a href="{{ route('users.index') }}" class="group rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-purple-50 text-purple-600">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <circle cx="12" cy="8" r="3" />
                                    <path d="M4 20a8 8 0 0116 0" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-base font-semibold text-gray-800">{{ __('Panel de usuarios') }}</div>
                                <div class="text-xs text-gray-500">{{ __('Administración básica') }}</div>
                            </div>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
