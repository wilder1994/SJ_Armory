<x-revista-guest-layout :title="__('Mis armas')">
    <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-slate-700">{{ __('Hola :name', ['name' => $temporaryUser->name]) }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ __('Acceso vigente hasta :date', ['date' => $expiresAt->timezone(config('app.timezone'))->format('d/m/Y H:i')]) }}</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold">{{ __('Tipo') }}</th>
                        <th class="px-3 py-2 text-left font-semibold">{{ __('Marca') }}</th>
                        <th class="px-3 py-2 text-left font-semibold">{{ __('Serie') }}</th>
                        <th class="px-3 py-2 text-left font-semibold">{{ __('Calibre') }}</th>
                        <th class="px-3 py-2 text-left font-semibold">{{ __('Permiso') }}</th>
                        <th class="px-3 py-2 text-center font-semibold">{{ __('Realizado') }}</th>
                        <th class="px-3 py-2 text-right font-semibold">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($rows as $row)
                        @php($weapon = $row['weapon'])
                        <tr>
                            <td class="px-3 py-2">{{ $weapon->weapon_type ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $weapon->brand ?? '—' }}</td>
                            <td class="px-3 py-2 font-medium">{{ $weapon->serial_number ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $weapon->caliber ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $weapon->permit_type ?? '—' }} {{ $weapon->permit_number }}</td>
                            <td class="px-3 py-2 text-center">
                                @if ($row['is_complete'])
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.55)]">✓</span>
                                @else
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-red-500/15 text-red-500 shadow-[0_0_10px_rgba(239,68,68,0.45)]">✕</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right">
                                <button
                                    type="button"
                                    class="revista-guest-upload rounded-lg bg-[#0b6fb6] px-2.5 py-1 text-xs font-bold text-white"
                                    data-weapon-id="{{ $weapon->id }}"
                                    data-state-url="{{ route('revista-armas.guest.weapons.staging-state', $weapon) }}"
                                    data-store-url="{{ route('revista-armas.guest.weapons.photos.store', $weapon) }}"
                                    data-serial="{{ $weapon->serial_number }}"
                                >{{ __('Ver') }}</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="revista-guest-upload-modal" class="fixed inset-0 z-[1050] hidden items-center justify-center bg-black/40 p-4">
        <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
            <div class="border-b px-4 py-3 font-semibold">{{ __('Fotos del arma') }} — <span id="revista-guest-serial"></span></div>
            <div id="revista-guest-slot-grid" class="grid grid-cols-2 gap-3 overflow-y-auto p-4"></div>
            <div class="flex justify-end border-t px-4 py-3">
                <button type="button" data-revista-guest-close class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold">{{ __('Cerrar') }}</button>
            </div>
        </div>
    </div>

    @include('revista-armas.partials.photo-capture-kit')

    @push('scripts')
    <script>
        (() => {
            const uploadModal = document.getElementById('revista-guest-upload-modal');
            const slotGrid = document.getElementById('revista-guest-slot-grid');
            let stateUrl = '';
            let activeStoreUrl = '';

            const reloadGuestGrid = async () => {
                if (!stateUrl) return;
                const res = await fetch(stateUrl, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                renderSlots(data);
                window.location.reload();
            };

            const capture = window.initRevistaPhotoCapture({
                csrfToken: @json(csrf_token()),
                onSuccess: reloadGuestGrid,
            });

            const renderSlots = (data) => {
                slotGrid.innerHTML = '';
                (data.slots || []).forEach((slot) => {
                    const cell = document.createElement('button');
                    cell.type = 'button';
                    cell.className = 'revista-slot-card rounded-lg border border-slate-200 p-2 text-left';
                    cell.innerHTML = slot.url
                        ? `<img src="${slot.url}" class="h-32 w-full rounded object-contain bg-slate-50"><div class="mt-1 text-xs font-semibold">${slot.label}</div>`
                        : `<div class="flex h-32 items-center justify-center rounded border border-dashed border-slate-300 text-xs text-slate-500">${slot.label}<br>{{ __('Tocar para capturar') }}</div>`;
                    cell.addEventListener('click', () => capture.openSlot(activeStoreUrl, slot.description));
                    slotGrid.appendChild(cell);
                });
            };

            const openModal = () => { uploadModal?.classList.remove('hidden'); uploadModal?.classList.add('flex'); };
            const closeModal = () => { uploadModal?.classList.add('hidden'); uploadModal?.classList.remove('flex'); };
            document.querySelector('[data-revista-guest-close]')?.addEventListener('click', closeModal);

            document.querySelectorAll('.revista-guest-upload').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    stateUrl = btn.dataset.stateUrl;
                    activeStoreUrl = btn.dataset.storeUrl;
                    document.getElementById('revista-guest-serial').textContent = btn.dataset.serial || '';
                    const res = await fetch(stateUrl, { headers: { 'Accept': 'application/json' } });
                    renderSlots(await res.json());
                    openModal();
                });
            });
        })();
    </script>
    @endpush
</x-revista-guest-layout>
