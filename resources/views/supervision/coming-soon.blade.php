<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <p class="sj-section-header__eyebrow">{{ __('Supervisión') }}</p>
                <h2 class="sj-section-header__title">{{ __('Recorrido de patrulla') }}</h2>
                <p class="sj-section-header__subtitle">{{ __('Este módulo estará disponible próximamente.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide">
            <article class="sj-ui-card">
                <div class="sj-ui-card__body p-6">
                    <p class="text-sm text-slate-600">
                        {{ __('Aquí se concentrará el control de recorridos, puntos de control y bitácora de supervisión en campo.') }}
                    </p>
                </div>
            </article>
        </div>
    </div>
</x-app-layout>
