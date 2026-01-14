<nav x-data="{ open: false }" class="sj-nav">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="-ml-36 shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block" />
                    </a>
                </div>
                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-36 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Inicio') }}
                    </x-nav-link>
                    @can('viewAny', App\Models\Weapon::class)
                        <x-nav-link :href="route('weapons.index')" :active="request()->routeIs('weapons.*')">
                            {{ __('Armamento') }}
                        </x-nav-link>
                    @endcan
                    @can('viewAny', App\Models\Client::class)
                        <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                            {{ __('Clientes') }}
                        </x-nav-link>
                    @endcan
                    @if (Auth::user()?->isAdmin())
                        <x-nav-link :href="route('portfolios.index')" :active="request()->routeIs('portfolios.*')">
                            {{ __('Carteras') }}
                        </x-nav-link>
                        <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                            {{ __('Reportes') }}
                        </x-nav-link>
                        <x-nav-link :href="route('alerts.documents')" :active="request()->routeIs('alerts.*')">
                            {{ __('Alertas') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-slate-100 bg-white/10 hover:bg-white/20 hover:text-white focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-100 hover:text-white hover:bg-white/10 focus:outline-none focus:bg-white/10 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="sj-mobile-menu hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Inicio') }}
            </x-responsive-nav-link>
            @can('viewAny', App\Models\Weapon::class)
                <x-responsive-nav-link :href="route('weapons.index')" :active="request()->routeIs('weapons.*')">
                    {{ __('Armamento') }}
                </x-responsive-nav-link>
            @endcan
            @can('viewAny', App\Models\Client::class)
                <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                    {{ __('Clientes') }}
                </x-responsive-nav-link>
            @endcan
            @if (Auth::user()?->isAdmin())
                <x-responsive-nav-link :href="route('portfolios.index')" :active="request()->routeIs('portfolios.*')">
                    {{ __('Carteras') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                    {{ __('Reportes') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('alerts.documents')" :active="request()->routeIs('alerts.*')">
                    {{ __('Alertas') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-white/10">
            <div class="px-4">
                <div class="font-medium text-base text-slate-100">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-slate-300">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Cerrar sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
