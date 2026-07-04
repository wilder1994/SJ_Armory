<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Editar chaleco') }}</h2>
                <p class="sj-section-header__subtitle">{{ $vest->serial_number }} · {{ $vest->client?->name }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('vests.show', $vest) }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al detalle') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide space-y-4">
            <div class="sj-ui-card p-6">
                <form
                    method="POST"
                    action="{{ route('vests.update', $vest) }}"
                    class="sj-form-panel"
                    data-vest-form
                    data-form-options-url="{{ route('vests.form-options') }}"
                    data-lock-device-responsible="{{ ! empty($lockDeviceResponsible) ? '1' : '0' }}"
                    data-fixed-responsible-name="{{ auth()->user()->name }}"
                    data-client-error="{{ $errors->first('client_id') }}"
                    @if ($errors->has('client_id') && str_contains((string) $errors->first('client_id'), 'Primero debe realizar la asignación del responsable.'))
                        data-show-missing-responsible-modal="1"
                    @endif
                >
                    @csrf
                    @method('PUT')
                    @include('vests.partials.form')
                    <div class="sj-form-actions">
                        <a href="{{ route('vests.show', $vest) }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                        <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Actualizar') }}</button>
                    </div>
                </form>
            </div>

            @include('vests.partials.photos')
        </div>
    </div>
</x-app-layout>
