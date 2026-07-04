<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Editar chaleco') }}</h2>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('vests.show', $vest) }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al detalle') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide">
            <div class="sj-ui-card p-6">
                <form method="POST" action="{{ route('vests.update', $vest) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('vests.partials.form')
                    <div class="flex justify-end">
                        <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Actualizar') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
