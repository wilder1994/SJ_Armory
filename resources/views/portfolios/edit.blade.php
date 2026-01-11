<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cartera de') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('portfolios.update', $user) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            @foreach ($clients as $client)
                                <label class="flex items-center gap-2 rounded border border-gray-200 p-2 text-sm">
                                    <input type="checkbox" name="clients[]" value="{{ $client->id }}" @checked(in_array($client->id, $assigned, true))>
                                    <span>{{ $client->name }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('portfolios.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Guardar cartera') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
