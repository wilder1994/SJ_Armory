<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar cliente') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <x-input-label for="name" :value="__('Razon social')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $client->name) }}" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="nit" :value="__('NIT')" />
                                <x-text-input id="nit" name="nit" type="text" class="mt-1 block w-full" value="{{ old('nit', $client->nit) }}" required />
                                <x-input-error :messages="$errors->get('nit')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Correo electrónico')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email', $client->email) }}" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="address" :value="__('Direccion')" />
                                <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" value="{{ old('address', $client->address) }}" />
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="neighborhood" :value="__('Barrio')" />
                                <x-text-input id="neighborhood" name="neighborhood" type="text" class="mt-1 block w-full" value="{{ old('neighborhood', $client->neighborhood) }}" />
                                <x-input-error :messages="$errors->get('neighborhood')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="city" :value="__('Ciudad')" />
                                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" value="{{ old('city', $client->city) }}" />
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="legal_representative" :value="__('Representante legal')" />
                                <x-text-input id="legal_representative" name="legal_representative" type="text" class="mt-1 block w-full" value="{{ old('legal_representative', $client->legal_representative) }}" />
                                <x-input-error :messages="$errors->get('legal_representative')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="contact_name" :value="__('Contacto')" />
                                <x-text-input id="contact_name" name="contact_name" type="text" class="mt-1 block w-full" value="{{ old('contact_name', $client->contact_name) }}" />
                                <x-input-error :messages="$errors->get('contact_name')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('clients.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Guardar cambios') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
