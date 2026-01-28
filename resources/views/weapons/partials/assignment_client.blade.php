<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">{{ __('Destino operativo') }}</h3>
            <div class="text-xs text-gray-500">
                {{ __('Cliente actual:') }}
                <span class="font-medium">
                    {{ $weapon->activeClientAssignment?->client?->name ?? __('Sin destino') }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('weapons.client_assignments.store', $weapon) }}" class="mt-4 space-y-4">
            @csrf

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="text-sm text-gray-600">{{ __('Cliente') }}</label>
                    <select name="client_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                        <option value="">{{ __('Seleccione') }}</option>
                        @foreach ($clientOptions as $client)
                            <option value="{{ $client->id }}" @selected($weapon->activeClientAssignment?->client_id === $client->id)>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                </div>

                @if (Auth::user()->isAdmin() && !$weapon->activeClientAssignment)
                    <div>
                        <label class="text-sm text-gray-600">{{ __('Responsable') }}</label>
                        <select name="responsible_user_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                            <option value="">{{ __('Seleccione') }}</option>
                            @foreach ($responsibles as $responsible)
                                <option value="{{ $responsible->id }}">{{ $responsible->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('responsible_user_id')" class="mt-2" />
                    </div>
                @else
                    <div>
                        <label class="text-sm text-gray-600">{{ __('Responsable') }}</label>
                        <div class="mt-1 rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            {{ $weapon->activeClientAssignment?->responsible?->name ?? Auth::user()->name }}
                        </div>
                    </div>
                @endif

                <div class="md:col-span-3">
                    <label class="text-sm text-gray-600">{{ __('Observaciones') }}</label>
                    <input type="text" name="reason" class="mt-1 block w-full rounded-md border-gray-300 text-sm" />
                    <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="submit" class="text-sm text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded">
                    {{ __('Actualizar destino') }}
                </button>
            </div>
        </form>
    </div>
</div>
