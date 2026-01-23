<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <div class="text-xs text-gray-500">{{ __('Codigo interno') }}</div>
                <div class="font-medium">{{ $weapon->internal_code }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Numero de serie') }}</div>
                <div class="font-medium">{{ $weapon->serial_number }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Tipo') }}</div>
                <div class="font-medium">{{ $weapon->weapon_type }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Calibre') }}</div>
                <div class="font-medium">{{ $weapon->caliber }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Marca') }}</div>
                <div class="font-medium">{{ $weapon->brand }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Modelo') }}</div>
                <div class="font-medium">{{ $weapon->model }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Responsable') }}</div>
                <div class="font-medium">{{ $weapon->activeClientAssignment?->responsible?->name ?? '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Tipo de propiedad') }}</div>
                <div class="font-medium">{{ $ownershipTypes[$weapon->ownership_type] ?? $weapon->ownership_type }}</div>
            </div>
            <div class="md:col-span-2">
                <div class="text-xs text-gray-500">{{ __('Entidad de propiedad') }}</div>
                <div class="font-medium">{{ $weapon->ownership_entity ?: '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Tipo de permiso') }}</div>
                <div class="font-medium">
                    @if ($weapon->permit_type === 'porte')
                        {{ __('Porte') }}
                    @elseif ($weapon->permit_type === 'tenencia')
                        {{ __('Tenencia') }}
                    @else
                        -
                    @endif
                </div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Numero de permiso') }}</div>
                <div class="font-medium">{{ $weapon->permit_number ?: '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Fecha de vencimiento') }}</div>
                <div class="font-medium">{{ $weapon->permit_expires_at?->format('Y-m-d') ?: '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Foto del permiso') }}</div>
                <div class="font-medium">{{ $weapon->permitFile?->original_name ?? '-' }}</div>
            </div>
            <div class="md:col-span-2">
                <div class="text-xs text-gray-500">{{ __('Notas') }}</div>
                <div class="font-medium whitespace-pre-line">{{ $weapon->notes ?: '-' }}</div>
            </div>
        </div>
    </div>
</div>
