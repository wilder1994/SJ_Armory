<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Cambio de contraseña obligatorio') }}</h2>
                <p class="sj-section-header__subtitle">{{ __('Por seguridad debe definir su propia contraseña antes de continuar.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell">
            <div class="sj-ui-card p-6 max-w-xl">
                <form method="POST" action="{{ route('password.force.update') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <x-password-reveal-input
                        label="{{ __('Nueva contraseña') }}"
                        name="password"
                        id="force_password"
                        autocomplete="new-password"
                        required
                    />

                    <x-password-reveal-input
                        label="{{ __('Confirmar contraseña') }}"
                        name="password_confirmation"
                        id="force_password_confirmation"
                        autocomplete="new-password"
                        required
                    />

                    <div class="sj-form-actions">
                        <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Establecer contraseña') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
