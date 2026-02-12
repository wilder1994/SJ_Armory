<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar arma') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('weapons.update', $weapon) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('weapons.partials.form', [
                            'weapon' => $weapon,
                            'ownershipTypes' => $ownershipTypes,
                            'showInternalCode' => true,
                            'requirePermitPhoto' => false,
                            'cancelUrl' => route('weapons.show', $weapon),
                            'submitLabel' => __('Guardar cambios'),
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>




