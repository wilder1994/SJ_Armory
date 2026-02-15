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

        <form method="POST" action="{{ route('weapons.client_assignments.store', $weapon) }}" class="mt-4 space-y-4" id="destination-operational-form">
            @csrf

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="text-sm text-gray-600">{{ __('Cliente') }}</label>
                    <select name="client_id" id="destination-client-select" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                        <option value="">{{ __('Seleccione') }}</option>
                        @foreach ($clientOptions as $client)
                            @php
                                $responsibleMeta = $clientResponsibleMap[$client->id] ?? ['id' => null, 'name' => null];
                            @endphp
                            <option
                                value="{{ $client->id }}"
                                data-responsible-id="{{ $responsibleMeta['id'] ?? '' }}"
                                data-responsible-name="{{ $responsibleMeta['name'] ?? '' }}"
                                @selected(old('client_id', $weapon->activeClientAssignment?->client_id) == $client->id)
                            >
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                </div>

                <div>
                    <label class="text-sm text-gray-600">{{ __('Responsable') }}</label>
                    <div id="destination-responsible-display" class="mt-1 rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                        {{ $weapon->activeClientAssignment?->responsible?->name ?? __('Sin responsable asignado') }}
                    </div>
                    <input type="hidden" id="destination-responsible-id" name="responsible_user_id" value="{{ $weapon->activeClientAssignment?->responsible_user_id }}">
                </div>

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

<div id="missing-responsible-modal" class="fixed inset-0 z-[1400] hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-lg bg-white p-5 shadow-xl">
        <h4 class="text-base font-semibold text-gray-900">{{ __('Atención') }}</h4>
        <p class="mt-2 text-sm text-gray-700">{{ __('Primero debe realizar la asignación del responsable.') }}</p>
        <div class="mt-4 flex justify-end">
            <button type="button" id="missing-responsible-modal-close" class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">
                {{ __('Aceptar') }}
            </button>
        </div>
    </div>
</div>

<script>
    (() => {
        const form = document.getElementById('destination-operational-form');
        const clientSelect = document.getElementById('destination-client-select');
        const responsibleDisplay = document.getElementById('destination-responsible-display');
        const responsibleIdInput = document.getElementById('destination-responsible-id');
        const modal = document.getElementById('missing-responsible-modal');
        const closeBtn = document.getElementById('missing-responsible-modal-close');
        if (!form || !clientSelect || !responsibleDisplay || !responsibleIdInput || !modal || !closeBtn) return;

        const showModal = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        const hideModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        const syncResponsible = () => {
            const option = clientSelect.selectedOptions?.[0];
            if (!option || !option.value) {
                responsibleDisplay.textContent = @json(__('Sin responsable asignado'));
                responsibleIdInput.value = '';
                return false;
            }

            const responsibleId = option.dataset.responsibleId || '';
            const responsibleName = option.dataset.responsibleName || '';

            responsibleIdInput.value = responsibleId;
            responsibleDisplay.textContent = responsibleName || @json(__('Sin responsable asignado'));

            return responsibleId !== '';
        };

        clientSelect.addEventListener('change', () => {
            if (!syncResponsible()) {
                showModal();
            }
        });

        form.addEventListener('submit', (event) => {
            if (!syncResponsible()) {
                event.preventDefault();
                showModal();
            }
        });

        closeBtn.addEventListener('click', hideModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) hideModal();
        });

        syncResponsible();

        @if ($errors->has('client_id') && str_contains((string) $errors->first('client_id'), 'Primero debe realizar la asignación del responsable.'))
            showModal();
        @endif
    })();
</script>
