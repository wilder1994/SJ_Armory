<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">{{ __('Transferir arma') }}</h3>
        </div>

        <form method="POST" action="{{ route('weapons.transfers.store', $weapon) }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-4">
            @csrf
            <div class="md:col-span-2">
                <label class="text-sm text-gray-600">{{ __('Destinatario') }}</label>
                <select name="to_user_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                    <option value="">{{ __('Seleccione') }}</option>
                    @foreach ($transferRecipients as $recipient)
                        <option value="{{ $recipient->id }}">{{ $recipient->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('to_user_id')" class="mt-2" />
            </div>
            @if (Auth::user()->isAdmin())
                <div class="md:col-span-2">
                    <label class="text-sm text-gray-600">{{ __('Nuevo cliente (opcional)') }}</label>
                    <select name="new_client_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                        <option value="">{{ __('Mantener cliente actual') }}</option>
                        @foreach ($portfolioClients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('new_client_id')" class="mt-2" />
                </div>
            @endif
            <div class="md:col-span-4">
                <label class="text-sm text-gray-600">{{ __('Observaciones') }}</label>
                <input type="text" name="note" spellcheck="true" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
            </div>
            <div class="md:col-span-4 flex items-center justify-end">
                <button type="submit" class="text-xs text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded">
                    {{ __('Enviar solicitud') }}
                </button>
            </div>
        </form>
    </div>
</div>
