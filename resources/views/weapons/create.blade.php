<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nueva arma') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('weapons.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @csrf

                        <div>
                            <x-input-label for="internal_code" :value="__('Código interno')" />
                            <x-text-input id="internal_code" name="internal_code" type="text" class="mt-1 block w-full" value="{{ old('internal_code') }}" required />
                            <x-input-error :messages="$errors->get('internal_code')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="serial_number" :value="__('Número de serie')" />
                            <x-text-input id="serial_number" name="serial_number" type="text" class="mt-1 block w-full" value="{{ old('serial_number') }}" required />
                            <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="weapon_type" :value="__('Tipo de arma')" />
                            <x-text-input id="weapon_type" name="weapon_type" type="text" class="mt-1 block w-full" value="{{ old('weapon_type') }}" required />
                            <x-input-error :messages="$errors->get('weapon_type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="caliber" :value="__('Calibre')" />
                            <x-text-input id="caliber" name="caliber" type="text" class="mt-1 block w-full" value="{{ old('caliber') }}" required />
                            <x-input-error :messages="$errors->get('caliber')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="brand" :value="__('Marca')" />
                            <x-text-input id="brand" name="brand" type="text" class="mt-1 block w-full" value="{{ old('brand') }}" required />
                            <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="model" :value="__('Modelo')" />
                            <x-text-input id="model" name="model" type="text" class="mt-1 block w-full" value="{{ old('model') }}" required />
                            <x-input-error :messages="$errors->get('model')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="operational_status" :value="__('Estado operativo')" />
                            <select id="operational_status" name="operational_status" class="mt-1 block w-full rounded-md border-gray-300">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('operational_status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('operational_status')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="ownership_type" :value="__('Tipo de propiedad')" />
                            <select id="ownership_type" name="ownership_type" class="mt-1 block w-full rounded-md border-gray-300">
                                @foreach ($ownershipTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('ownership_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('ownership_type')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="ownership_entity" :value="__('Entidad de propiedad (si aplica)')" />
                            <x-text-input id="ownership_entity" name="ownership_entity" type="text" class="mt-1 block w-full" value="{{ old('ownership_entity') }}" />
                            <x-input-error :messages="$errors->get('ownership_entity')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="notes" :value="__('Notas')" />
                            <textarea id="notes" name="notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2 flex justify-end gap-2">
                            <a href="{{ route('weapons.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Guardar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
