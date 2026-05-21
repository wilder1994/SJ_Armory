<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ __('Revista armas') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Revise fotos en staging y asigne acceso temporal a colaboradores de campo.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('revista-armas.temporary-users.index') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    {{ __('Usuarios temporales') }}
                </a>
                <button type="button" id="revista-open-assign" class="rounded-lg bg-[#0b6fb6] px-3 py-2 text-sm font-bold text-white hover:bg-[#085a93]">
                    {{ __('Asignar acceso temporal') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <form method="GET" class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <label for="temporary_photo_user_id" class="block text-sm font-medium text-slate-700">{{ __('Usuario temporal (columna Realizado)') }}</label>
                    <select name="temporary_photo_user_id" id="temporary_photo_user_id" class="mt-1 min-w-[16rem] rounded-lg border-slate-300 text-sm">
                        <option value="">{{ __('Seleccione...') }}</option>
                        @foreach ($temporaryUsers as $tu)
                            <option value="{{ $tu->id }}" @selected($selectedTemporaryUserId === $tu->id)>{{ $tu->name }} ({{ $tu->email }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700">{{ __('Filtrar') }}</button>
            </form>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600">{{ __('Tipo') }}</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600">{{ __('Marca') }}</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600">{{ __('Serie') }}</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600">{{ __('Calibre') }}</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600">{{ __('Tipo permiso') }}</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600">{{ __('Nº permiso') }}</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600">{{ __('Vencimiento') }}</th>
                                <th class="px-3 py-2 text-center font-semibold text-slate-600">{{ __('Realizado') }}</th>
                                <th class="px-3 py-2 text-right font-semibold text-slate-600">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($rows as $row)
                                @php($weapon = $row['weapon'])
                                @php($done = $selectedTemporaryUserId ? ($row['completions'][$selectedTemporaryUserId] ?? false) : false)
                                <tr>
                                    <td class="px-3 py-2">{{ $weapon->weapon_type ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->brand ?? '—' }}</td>
                                    <td class="px-3 py-2 font-medium">{{ $weapon->serial_number ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->caliber ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->permit_type ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->permit_number ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->permit_expires_at?->format('Y-m-d') ?? '—' }}</td>
                                    <td class="px-3 py-2 text-center">
                                        @if ($selectedTemporaryUserId)
                                            @if ($done)
                                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.55)]" title="{{ __('Completo') }}">✓</span>
                                            @else
                                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-red-500/15 text-red-500 shadow-[0_0_10px_rgba(239,68,68,0.45)]" title="{{ __('Pendiente') }}">✕</span>
                                            @endif
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        @if ($selectedTemporaryUserId)
                                            <button
                                                type="button"
                                                class="revista-review-btn rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-semibold text-[#0b6fb6] hover:bg-slate-50"
                                                data-review-url="{{ route('revista-armas.review', [$weapon, $selectedTemporaryUserId]) }}"
                                                data-approve-url="{{ route('revista-armas.review.approve', [$weapon, $selectedTemporaryUserId]) }}"
                                                data-reject-url="{{ route('revista-armas.review.reject', [$weapon, $selectedTemporaryUserId]) }}"
                                                data-serial="{{ $weapon->serial_number }}"
                                            >{{ __('Ver') }}</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-3 py-8 text-center text-slate-500">{{ __('No hay armas en su alcance.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Asignar acceso --}}
    <div id="revista-assign-modal" class="fixed inset-0 z-[1050] hidden items-center justify-center bg-black/40 p-4">
        <div class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
            <div class="border-b px-4 py-3 font-semibold text-slate-900">{{ __('Asignar acceso temporal') }}</div>
            <form id="revista-assign-form" method="POST" action="{{ route('revista-armas.access.store') }}" class="flex min-h-0 flex-1 flex-col">
                @csrf
                <div class="space-y-4 overflow-y-auto p-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Usuario temporal') }}</label>
                        <select name="temporary_photo_user_id" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                            <option value="">{{ __('Seleccione...') }}</option>
                            @foreach ($temporaryUsers as $tu)
                                <option value="{{ $tu->id }}">{{ $tu->name }} — {{ $tu->email }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">
                            <a href="{{ route('revista-armas.temporary-users.create') }}" class="text-[#0b6fb6] font-semibold">{{ __('Crear nuevo usuario temporal') }}</a>
                        </p>
                    </div>
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700">{{ __('Armas visibles para este acceso') }}</span>
                            <label class="text-xs text-slate-600"><input type="checkbox" id="revista-select-all-weapons" class="rounded border-slate-300"> {{ __('Seleccionar todas') }}</label>
                        </div>
                        <div class="max-h-52 overflow-y-auto rounded-lg border border-slate-200 p-2 space-y-1">
                            @foreach ($rows as $row)
                                @php($w = $row['weapon'])
                                <label class="flex items-center gap-2 rounded px-2 py-1 text-sm hover:bg-slate-50">
                                    <input type="checkbox" name="weapon_ids[]" value="{{ $w->id }}" class="revista-weapon-cb rounded border-slate-300">
                                    <span>{{ $w->serial_number }} — {{ $w->weapon_type }} {{ $w->brand }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t px-4 py-3">
                    <button type="button" data-revista-assign-cancel class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700">{{ __('Cancelar') }}</button>
                    <button type="submit" class="rounded-lg bg-[#0b6fb6] px-3 py-2 text-sm font-bold text-white">{{ __('Enviar') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Éxito acceso --}}
    @if (session('revista_access_success'))
        @php($ok = session('revista_access_success'))
        <div id="revista-success-modal" class="fixed inset-0 z-[1060] flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl">
                <h3 class="text-lg font-bold text-slate-900">{{ __('Acceso creado') }}</h3>
                <p class="mt-2 text-sm text-slate-600">{{ __('Copie y envíe estos datos al colaborador. También se envió un correo si el servidor de correo está configurado.') }}</p>
                <textarea id="revista-success-copy" readonly rows="6" class="mt-3 w-full rounded-lg border-slate-300 text-sm">@foreach ([
                    __('Enlace') . ': ' . $ok['login_url'],
                    __('Correo') . ': ' . $ok['email'],
                    __('Código') . ': ' . $ok['code'],
                    __('Válido hasta') . ': ' . $ok['expires_at'],
                ] as $line){{ $line }}
@endforeach</textarea>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" id="revista-copy-success" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold">{{ __('Copiar') }}</button>
                    <button type="button" onclick="document.getElementById('revista-success-modal').remove()" class="rounded-lg bg-[#0b6fb6] px-3 py-2 text-sm font-bold text-white">{{ __('Cerrar') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Revisión --}}
    <div id="revista-review-modal" class="fixed inset-0 z-[1050] hidden items-center justify-center bg-black/40 p-4">
        <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
            <div class="border-b px-4 py-3">
                <h3 class="font-semibold text-slate-900">{{ __('Revisión de fotos') }} — <span id="revista-review-serial"></span></h3>
            </div>
            <div id="revista-review-grid" class="grid grid-cols-2 gap-3 overflow-y-auto p-4"></div>
            <div class="flex justify-end gap-2 border-t px-4 py-3">
                <button type="button" id="revista-review-reject" class="rounded-lg border border-red-300 px-3 py-2 text-sm font-semibold text-red-700">{{ __('Rechazar') }}</button>
                <button type="button" id="revista-review-approve" class="rounded-lg bg-[#0b6fb6] px-3 py-2 text-sm font-bold text-white">{{ __('Actualizar') }}</button>
                <button type="button" data-revista-review-close class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700">{{ __('Cerrar') }}</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (() => {
            const assignModal = document.getElementById('revista-assign-modal');
            document.getElementById('revista-open-assign')?.addEventListener('click', () => {
                assignModal?.classList.remove('hidden');
                assignModal?.classList.add('flex');
            });
            document.querySelector('[data-revista-assign-cancel]')?.addEventListener('click', () => {
                assignModal?.classList.add('hidden');
                assignModal?.classList.remove('flex');
            });
            document.getElementById('revista-select-all-weapons')?.addEventListener('change', (e) => {
                document.querySelectorAll('.revista-weapon-cb').forEach((cb) => { cb.checked = e.target.checked; });
            });
            document.getElementById('revista-copy-success')?.addEventListener('click', () => {
                const ta = document.getElementById('revista-success-copy');
                ta?.select();
                navigator.clipboard?.writeText(ta.value);
            });

            const reviewModal = document.getElementById('revista-review-modal');
            const reviewGrid = document.getElementById('revista-review-grid');
            let approveUrl = '';
            let rejectUrl = '';

            const closeReview = () => { reviewModal?.classList.add('hidden'); reviewModal?.classList.remove('flex'); };
            document.querySelectorAll('[data-revista-review-close]').forEach((b) => b.addEventListener('click', closeReview));

            document.querySelectorAll('.revista-review-btn').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    approveUrl = btn.dataset.approveUrl;
                    rejectUrl = btn.dataset.rejectUrl;
                    document.getElementById('revista-review-serial').textContent = btn.dataset.serial || '';
                    const res = await fetch(btn.dataset.reviewUrl, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    reviewGrid.innerHTML = '';
                    (data.slots || []).forEach((slot) => {
                        const cell = document.createElement('div');
                        cell.className = 'rounded-lg border border-slate-200 p-2';
                        cell.innerHTML = slot.url
                            ? `<img src="${slot.url}" class="h-36 w-full rounded object-contain bg-slate-50"><div class="mt-1 text-xs font-medium text-slate-600">${slot.label}</div>`
                            : `<div class="flex h-36 items-center justify-center rounded border border-dashed border-slate-300 text-xs text-slate-400">${slot.label}<br>{{ __('Sin imagen') }}</div>`;
                        reviewGrid.appendChild(cell);
                    });
                    reviewModal?.classList.remove('hidden');
                    reviewModal?.classList.add('flex');
                });
            });

            const postAction = (url) => fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json',
                },
            }).then(() => window.location.reload());

            document.getElementById('revista-review-approve')?.addEventListener('click', () => {
                if (approveUrl && confirm(@json(__('¿Actualizar las imágenes oficiales del arma con estas fotos?')))) postAction(approveUrl);
            });
            document.getElementById('revista-review-reject')?.addEventListener('click', () => {
                if (rejectUrl && confirm(@json(__('¿Rechazar y eliminar las fotos en revisión?')))) postAction(rejectUrl);
            });
        })();
    </script>
    @endpush
</x-app-layout>
