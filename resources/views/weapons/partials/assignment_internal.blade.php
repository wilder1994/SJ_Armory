<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">{{ __('Asignación interna') }}</h3>
        </div>

        <div class="mt-3 text-sm text-gray-700">
            @if ($weapon->activePostAssignment)
                <div>{{ __('Puesto activo:') }} <span class="font-medium">{{ $weapon->activePostAssignment->post?->name }}</span></div>
            @elseif ($weapon->activeWorkerAssignment)
                <div>{{ __('Trabajador activo:') }} <span class="font-medium">{{ $weapon->activeWorkerAssignment->worker?->name }}</span></div>
            @else
                <div class="text-gray-500">{{ __('Sin asignacion interna.') }}</div>
            @endif
        </div>

        @if (!$weapon->activeClientAssignment)
            <div class="mt-3 rounded bg-amber-50 p-3 text-sm text-amber-700">
                {{ __('Debe asignar un cliente/responsable antes de asignar puesto o trabajador.') }}
            </div>
        @endif

        @if (session('replace_warning'))
            <div class="mt-3 rounded bg-amber-50 p-3 text-sm text-amber-700">
                {{ session('replace_message') }}
            </div>
        @endif

        <form method="POST" action="{{ route('weapons.internal_assignments.store', $weapon) }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-4" data-has-active="{{ $weapon->activePostAssignment || $weapon->activeWorkerAssignment ? '1' : '0' }}">
            @csrf
            <input type="hidden" name="replace" value="0">
            <div class="md:col-span-2">
                <label class="text-sm text-gray-600">{{ __('Puesto') }}</label>
                <select name="post_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                    <option value="">{{ __('Seleccione') }}</option>
                    @foreach ($posts as $post)
                        <option value="{{ $post->id }}">{{ $post->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('post_id')" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm text-gray-600">{{ __('Trabajador') }}</label>
                <select name="worker_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                    <option value="">{{ __('Seleccione') }}</option>
                    @foreach ($workers as $worker)
                        <option value="{{ $worker->id }}">{{ $worker->name }} ({{ $worker->role }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('worker_id')" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm text-gray-600">{{ __('Fecha de entrega') }}</label>
                <input type="date" name="start_at" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                <x-input-error :messages="$errors->get('start_at')" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm text-gray-600">{{ __('Observaciones') }}</label>
                <input type="text" name="reason" spellcheck="true" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm text-gray-600">{{ __('Cant. munición') }}</label>
                <input type="number" name="ammo_count" min="0" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                <x-input-error :messages="$errors->get('ammo_count')" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm text-gray-600">{{ __('Cnt. proveedor') }}</label>
                <input type="number" name="provider_count" min="0" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                <x-input-error :messages="$errors->get('provider_count')" class="mt-2" />
            </div>
            <div class="md:col-span-4 flex items-center justify-end gap-2">
                @if ($weapon->activePostAssignment || $weapon->activeWorkerAssignment)
                    <a href="#" class="text-xs text-red-600 hover:text-red-900" onclick="event.preventDefault(); document.getElementById('retire-internal-form').submit();">
                        {{ __('Retirar asignación') }}
                    </a>
                @endif
                <button type="submit" class="text-xs text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded" @disabled(!$weapon->activeClientAssignment)>
                    {{ $weapon->activePostAssignment || $weapon->activeWorkerAssignment ? __('Reemplazar') : __('Asignar') }}
                </button>
            </div>
        </form>

        @if ($weapon->activePostAssignment || $weapon->activeWorkerAssignment)
            <form id="retire-internal-form" method="POST" action="{{ route('weapons.internal_assignments.retire', $weapon) }}" class="hidden">
                @csrf
                @method('PATCH')
            </form>
        @endif
    </div>
</div>

<script>
    (() => {
        const form = document.querySelector('form[data-has-active]');
        if (!form) return;

        form.addEventListener('submit', (event) => {
            const hasActive = form.dataset.hasActive === '1';
            const postId = form.querySelector('select[name="post_id"]')?.value;
            const workerId = form.querySelector('select[name="worker_id"]')?.value;
            const replaceInput = form.querySelector('input[name="replace"]');

            if (!hasActive || !replaceInput) {
                return;
            }

            if (!postId && !workerId) {
                return;
            }

            const shouldReplace = window.confirm(@json(__('Ya existe una asignación interna activa. ¿Deseas reemplazarla?')));
            if (!shouldReplace) {
                event.preventDefault();
                return;
            }

            replaceInput.value = '1';
        });
    })();
</script>




