<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Nuevo chaleco') }}</h2>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('vests.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide">
            <div class="sj-ui-card p-6">
                <form method="POST" action="{{ route('vests.store') }}" class="space-y-6">
                    @csrf
                    @include('vests.partials.form')
                    <div class="flex justify-end">
                        <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
