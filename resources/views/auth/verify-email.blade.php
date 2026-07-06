<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Gracias por registrarte. Antes de empezar, verifica tu correo haciendo clic en el enlace que te enviamos. Si no recibiste el correo, te enviaremos otro.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('Se envió un nuevo enlace de verificación al correo registrado.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Reenviar correo de verificación') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="sj-ui-link sj-ui-link--muted">
                {{ __('Cerrar sesión') }}
            </button>
        </form>
    </div>
</x-guest-layout>




