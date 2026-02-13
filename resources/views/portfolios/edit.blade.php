<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cartera de') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('portfolios.update', $user) }}" class="space-y-4" id="portfolio-update-form">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                <ul class="list-disc space-y-1 pl-4">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            @foreach ($clients as $client)
                                @php
                                    $isAssigned = in_array($client->id, $assigned, true);
                                    $blockedCount = $blockedClientCounts[$client->id] ?? 0;
                                    $isBlocked = $blockedCount > 0;
                                @endphp
                                <label class="flex items-center gap-2 rounded border border-gray-200 p-2 text-sm">
                                    <input
                                        type="checkbox"
                                        name="clients[]"
                                        value="{{ $client->id }}"
                                        @checked($isAssigned)
                                        data-client-checkbox
                                    >
                                    <span>{{ $client->name }}</span>
                                    @if ($isBlocked)
                                        <span class="text-xs text-red-500">
                                            {{ __('Tiene armas asignadas') }} ({{ $blockedCount }})
                                        </span>
                                    @endif
                                </label>
                            @endforeach
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('portfolios.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Guardar cartera') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold">{{ __('Transferir cartera') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Seleccione los clientes y el responsable destino para transferir la cartera completa.') }}
                    </p>

                    <form method="POST" action="{{ route('portfolios.transfer', $user) }}" class="mt-4 space-y-4" id="portfolio-transfer-form">
                        @csrf

                        <div>
                            <label class="text-sm text-gray-600">{{ __('Responsable destino') }}</label>
                            <select name="to_user_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                                <option value="">{{ __('Seleccione') }}</option>
                                @foreach ($responsibles as $responsible)
                                    @if ($responsible->id !== $user->id)
                                        <option value="{{ $responsible->id }}">{{ $responsible->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="submit" class="text-sm text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded" id="portfolio-transfer-submit">
                                {{ __('Transferir cartera') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    (() => {
        const transferForm = document.getElementById('portfolio-transfer-form');
        const destinationSelect = transferForm?.querySelector('select[name="to_user_id"]');
        const clientCheckboxes = document.querySelectorAll('[data-client-checkbox]');

        if (!transferForm || !clientCheckboxes.length) return;

        transferForm.addEventListener('submit', (event) => {
            const selected = Array.from(clientCheckboxes).filter((checkbox) => checkbox.checked);
            if (selected.length === 0) {
                event.preventDefault();
                alert(@json(__('Seleccione al menos un cliente para transferir.')));
                return;
            }

            transferForm.querySelectorAll('input[name="clients[]"]').forEach((input) => input.remove());

            selected.forEach((checkbox) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'clients[]';
                hidden.value = checkbox.value;
                transferForm.appendChild(hidden);
            });

            const count = selected.length;
            const destinationName = destinationSelect?.selectedOptions?.[0]?.textContent?.trim() || @json(__('el nuevo responsable'));
            const message = @json(__('¿Confirmas la transferencia de :count cliente(s) a :destination?'))
                .replace(':count', count)
                .replace(':destination', destinationName);
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    })();
</script>




