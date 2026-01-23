<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">{{ __('Destino operativo') }}</h3>
        </div>

        <div class="mt-3 text-sm text-gray-700">
            <div>{{ __('Cliente actual:') }}
                <span class="font-medium">
                    {{ $weapon->activeClientAssignment?->client?->name ?? __('Sin destino') }}
                </span>
            </div>
            @if ($weapon->activeClientAssignment)
                <div class="text-gray-500">{{ __('Desde:') }} {{ $weapon->activeClientAssignment->start_at?->format('Y-m-d') }}</div>
            @endif
        </div>

        <form method="POST" action="{{ route('weapons.assignments.store', $weapon) }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-4">
            @csrf
            <div class="md:col-span-1">
                <label class="text-sm text-gray-600">{{ __('Responsable') }}</label>
                <select name="responsible_user_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                    <option value="">{{ __('Seleccione') }}</option>
                    @foreach ($responsibles as $responsible)
                        <option value="{{ $responsible->id }}" @selected($weapon->activeClientAssignment?->responsible_user_id === $responsible->id)>
                            {{ $responsible->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('responsible_user_id')" class="mt-2" />
            </div>
            <div class="md:col-span-1">
                <label class="text-sm text-gray-600">{{ __('Cliente') }}</label>
                <select name="client_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                    <option value="">{{ __('Seleccione') }}</option>
                    @foreach ($portfolioClients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
            </div>
            <div class="md:col-span-1">
                <label class="text-sm text-gray-600">{{ __('Fecha de entrega') }}</label>
                <input type="date" name="start_at" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                <x-input-error :messages="$errors->get('start_at')" class="mt-2" />
            </div>
            <div class="md:col-span-1">
                <label class="text-sm text-gray-600">{{ __('Observaciones') }}</label>
                <input type="text" name="reason" spellcheck="true" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
            </div>
            <div class="md:col-span-4 flex items-center justify-between">
                <div class="text-xs text-gray-500">
                    {{ __('Nivel actual:') }} {{ Auth::user()->responsibilityLevel?->level ?? '-' }}
                </div>
                <div class="flex items-center gap-2">
                    @if ($weapon->activeClientAssignment && (Auth::user()->isAdmin() || (Auth::user()->responsibilityLevel?->level ?? 0) >= 3))
                        <a href="#" class="text-xs text-red-600 hover:text-red-900" onclick="event.preventDefault(); document.getElementById('retire-destino-form').submit();">
                            {{ __('Retirar destino') }}
                        </a>
                    @endif
                    <button type="submit" class="text-xs text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded">
                        {{ $weapon->activeClientAssignment ? __('Reasignar') : __('Asignar') }}
                    </button>
                </div>
            </div>
        </form>
        @if ($weapon->activeClientAssignment && (Auth::user()->isAdmin() || (Auth::user()->responsibilityLevel?->level ?? 0) >= 3))
            <form id="retire-destino-form" method="POST" action="{{ route('weapons.assignments.retire', $weapon) }}" class="hidden">
                @csrf
                @method('PATCH')
            </form>
        @endif
    </div>
</div>
