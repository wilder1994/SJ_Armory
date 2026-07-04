<x-app-layout header-compact>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-lg font-semibold leading-tight text-gray-900">{{ $vest->serial_number }}</h1>
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $alert['badge_class'] }}">{{ $alert['state'] }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('update', $vest)
                    <a href="{{ route('vests.edit', $vest) }}" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm">{{ __('Editar') }}</a>
                @endcan
                <a href="{{ route('vests.index', request()->only('alert', 'q', 'client_id')) }}" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg bg-green-50 border border-green-200 p-4 mb-6 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <section class="sj-ui-card p-5">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-4">{{ __('Datos del chaleco') }}</h3>
                    <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        <div><dt class="text-gray-500">{{ __('Marca') }}</dt><dd class="font-medium text-gray-900">{{ $vest->brand ?: '—' }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('Lote') }}</dt><dd class="font-medium text-gray-900">{{ $vest->batch ?: '—' }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('Talla') }}</dt><dd class="font-medium text-gray-900">{{ $vest->size ?: '—' }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('Fabricación') }}</dt><dd class="font-medium text-gray-900">{{ $vest->manufactured_at?->format('d/m/Y') ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('Vencimiento') }}</dt><dd class="font-medium text-gray-900">{{ $vest->expires_at?->format('d/m/Y') ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('Días restantes') }}</dt><dd class="font-medium {{ $alert['text_class'] }}">{{ $alert['days_remaining'] ?? '—' }}</dd></div>
                    </dl>
                </section>

                <section class="sj-ui-card p-5">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-4">{{ __('Asignación') }}</h3>
                    <dl class="grid grid-cols-1 gap-3 text-sm">
                        <div><dt class="text-gray-500">{{ __('Cliente') }}</dt><dd class="font-medium text-gray-900">{{ $vest->client?->name ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('Trabajador') }}</dt><dd class="font-medium text-gray-900">{{ $vest->worker?->name ?? __('Sin asignar') }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('Cédula') }}</dt><dd class="font-medium text-gray-900">{{ $vest->worker?->document ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('Puesto') }}</dt><dd class="font-medium text-gray-900">{{ $vest->post?->name ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('Responsable dispositivo') }}</dt><dd class="font-medium text-gray-900">{{ $vest->device_responsible ?: '—' }}</dd></div>
                    </dl>
                </section>
            </div>

            @if ($vest->notes)
                <section class="mt-5 sj-ui-card p-5">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-2">{{ __('Notas') }}</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $vest->notes }}</p>
                </section>
            @endif

            <div class="mt-6">
                @include('vests.partials.photos')
            </div>
        </div>
    </div>
</x-app-layout>
