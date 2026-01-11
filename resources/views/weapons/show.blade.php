<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalle de arma') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('weapons.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    {{ __('Volver') }}
                </a>
                @can('update', $weapon)
                    <a href="{{ route('weapons.edit', $weapon) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                        {{ __('Editar') }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <div class="text-xs text-gray-500">{{ __('Código interno') }}</div>
                            <div class="font-medium">{{ $weapon->internal_code }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">{{ __('Número de serie') }}</div>
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
                            <div class="text-xs text-gray-500">{{ __('Estado operativo') }}</div>
                            <div class="font-medium">{{ $statuses[$weapon->operational_status] ?? $weapon->operational_status }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">{{ __('Tipo de propiedad') }}</div>
                            <div class="font-medium">{{ $ownershipTypes[$weapon->ownership_type] ?? $weapon->ownership_type }}</div>
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-xs text-gray-500">{{ __('Entidad de propiedad') }}</div>
                            <div class="font-medium">{{ $weapon->ownership_entity ?: '-' }}</div>
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-xs text-gray-500">{{ __('Notas') }}</div>
                            <div class="font-medium whitespace-pre-line">{{ $weapon->notes ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if (Auth::user()?->isAdmin())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold">{{ __('Estado operativo') }}</h3>
                        </div>
                        <form method="POST" action="{{ route('weapons.status.update', $weapon) }}" class="mt-3 flex flex-wrap items-end gap-3">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="text-sm text-gray-600">{{ __('Estado') }}</label>
                                <select name="operational_status" class="mt-1 block rounded-md border-gray-300 text-sm">
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}" @selected($weapon->operational_status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('operational_status')" class="mt-2" />
                            </div>
                            @if ($weapon->activeClientAssignment && $weapon->operational_status !== 'assigned')
                                <div class="text-xs text-amber-600">
                                    {{ __('Sugerencia: hay un destino activo, considere marcar como "Asignada".') }}
                                </div>
                            @endif
                            <button type="submit" class="text-xs text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded">
                                {{ __('Actualizar estado') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @can('assignToClient', $weapon)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold">{{ __('Destino operativo') }}</h3>
                        </div>

                        <div class="mt-3 text-sm text-gray-700">
                            <div>{{ __('Cliente actual:') }}
                                <span class="font-medium">
                                    {{ $weapon->activeClientAssignment?->client?->name ?? __('Sin destino') }}
                                </span>
                            </div>
                            @if ($weapon->activeClientAssignment)
                                <div class="text-gray-500">{{ __('Desde:') }} {{ $weapon->activeClientAssignment->start_at?->format('Y-m-d H:i') }}</div>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('weapons.assignments.store', $weapon) }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                            @csrf
                            <div class="md:col-span-1">
                                <label class="text-sm text-gray-600">{{ __('Cliente') }}</label>
                                <select name="client_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                                    <option value="">{{ __('Seleccione') }}</option>
                                    @foreach ($portfolioClients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                            </div>
                            <div class="md:col-span-1">
                                <label class="text-sm text-gray-600">{{ __('Inicio') }}</label>
                                <input type="datetime-local" name="start_at" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                <x-input-error :messages="$errors->get('start_at')" class="mt-2" />
                            </div>
                            <div class="md:col-span-1">
                                <label class="text-sm text-gray-600">{{ __('Motivo') }}</label>
                                <input type="text" name="reason" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                            </div>
                            <div class="md:col-span-3 flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    {{ __('Nivel actual:') }} {{ Auth::user()->responsibilityLevel?->level ?? '-' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($weapon->activeClientAssignment && (Auth::user()->isAdmin() || (Auth::user()->responsibilityLevel?->level ?? 0) >= 3))
                                        <a href="#" class="text-xs text-red-600 hover:text-red-900" onclick="event.preventDefault(); document.getElementById('retire-destino-form').submit();">
                                            {{ __('Retirar destino') }}
                                        </a>
                                    @endif
                                    <button type="submit" class="text-xs text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded">
                                        {{ $weapon->activeClientAssignment ? __('Reasignar') : __('Asignar') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                        @if ($weapon->activeClientAssignment && (Auth::user()->isAdmin() || (Auth::user()->responsibilityLevel?->level ?? 0) >= 3))
                            <form id="retire-destino-form" method="POST" action="{{ route('weapons.assignments.retire', $weapon) }}" class="hidden">
                                @csrf
                                @method('PATCH')
                            </form>
                        @endif
                    </div>
                </div>
            @endcan

            @if (Auth::user()?->isAdmin())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold">{{ __('Custodia primaria') }}</h3>
                        </div>

                        <div class="mt-3 text-sm text-gray-700">
                            <div>{{ __('Custodio actual:') }}
                                <span class="font-medium">
                                    {{ $weapon->activeCustody?->custodian?->name ?? __('Sin asignar') }}
                                </span>
                            </div>
                            @if ($weapon->activeCustody)
                                <div class="text-gray-500">{{ __('Desde:') }} {{ $weapon->activeCustody->start_at?->format('Y-m-d H:i') }}</div>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('weapons.custodies.store', $weapon) }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                            @csrf
                            <div class="md:col-span-1">
                                <label class="text-sm text-gray-600">{{ __('Responsable') }}</label>
                                <select name="custodian_user_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                                    <option value="">{{ __('Seleccione') }}</option>
                                    @foreach ($responsibles as $responsible)
                                        <option value="{{ $responsible->id }}">{{ $responsible->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('custodian_user_id')" class="mt-2" />
                            </div>
                            <div class="md:col-span-1">
                                <label class="text-sm text-gray-600">{{ __('Inicio') }}</label>
                                <input type="datetime-local" name="start_at" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                <x-input-error :messages="$errors->get('start_at')" class="mt-2" />
                            </div>
                            <div class="md:col-span-1">
                                <label class="text-sm text-gray-600">{{ __('Motivo') }}</label>
                                <input type="text" name="reason" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                            </div>
                            <div class="md:col-span-3 flex justify-end">
                                <x-primary-button class="text-xs">
                                    {{ __('Asignar / Cambiar custodia') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">{{ __('Fotos') }}</h3>
                        @can('update', $weapon)
                            <form method="POST" action="{{ route('weapons.photos.store', $weapon) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                                @csrf
                                <input type="file" name="photo" required class="text-sm">
                                <label class="flex items-center gap-1 text-sm text-gray-600">
                                    <input type="checkbox" name="is_primary" value="1" class="rounded">
                                    {{ __('Primaria') }}
                                </label>
                                <x-primary-button class="text-xs">
                                    {{ __('Subir') }}
                                </x-primary-button>
                            </form>
                        @endcan
                    </div>

                    @if ($errors->has('photo'))
                        <div class="mt-2 text-sm text-red-600">{{ $errors->first('photo') }}</div>
                    @endif

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                        @forelse ($weapon->photos as $photo)
                            <div class="border rounded-lg p-3">
                                @if ($photo->file)
                                    <img src="{{ Storage::disk($photo->file->disk)->url($photo->file->path) }}" alt="Foto" class="h-40 w-full object-cover rounded">
                                @endif
                                <div class="mt-2 flex items-center justify-between text-sm">
                                    <span class="text-gray-600">
                                        {{ $photo->is_primary ? __('Primaria') : __('Secundaria') }}
                                    </span>
                                    @can('update', $weapon)
                                        <div class="flex items-center gap-2">
                                            @if (!$photo->is_primary)
                                                <form method="POST" action="{{ route('weapons.photos.primary', [$weapon, $photo]) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="text-indigo-600 hover:text-indigo-900">
                                                        {{ __('Marcar primaria') }}
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('weapons.photos.destroy', [$weapon, $photo]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 hover:text-red-900" onclick="return confirm('¿Eliminar foto?')">
                                                    {{ __('Eliminar') }}
                                                </button>
                                            </form>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">{{ __('Sin fotos cargadas.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">{{ __('Documentos') }}</h3>
                        @can('update', $weapon)
                            <form method="POST" action="{{ route('weapons.documents.store', $weapon) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="file" name="document" required class="text-sm">
                                <select name="doc_type" class="rounded-md border-gray-300 text-sm">
                                    @foreach ($docTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="date" name="valid_until" class="rounded-md border-gray-300 text-sm" placeholder="{{ __('Vence') }}">
                                <input type="date" name="revalidation_due_at" class="rounded-md border-gray-300 text-sm" placeholder="{{ __('Revalidación') }}">
                                <input type="text" name="restrictions" class="rounded-md border-gray-300 text-sm" placeholder="{{ __('Restricciones') }}">
                                <input type="text" name="status" class="rounded-md border-gray-300 text-sm" placeholder="{{ __('Estado') }}">
                                <x-primary-button class="text-xs">
                                    {{ __('Subir') }}
                                </x-primary-button>
                            </form>
                        @endcan
                    </div>

                    @if ($errors->has('document'))
                        <div class="mt-2 text-sm text-red-600">{{ $errors->first('document') }}</div>
                    @endif

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Tipo') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Vence') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Revalidación') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Restricciones') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Estado') }}</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-600">{{ __('Acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($weapon->documents as $document)
                                    <tr>
                                        <td class="px-3 py-2">{{ $docTypes[$document->doc_type] ?? $document->doc_type }}</td>
                                        <td class="px-3 py-2">{{ optional($document->valid_until)->format('Y-m-d') }}</td>
                                        <td class="px-3 py-2">{{ optional($document->revalidation_due_at)->format('Y-m-d') }}</td>
                                        <td class="px-3 py-2">{{ $document->restrictions ?: '-' }}</td>
                                        <td class="px-3 py-2">{{ $document->status ?: '-' }}</td>
                                        <td class="px-3 py-2 text-right space-x-2">
                                            <a href="{{ route('weapons.documents.download', [$weapon, $document]) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ __('Descargar') }}
                                            </a>
                                            @can('update', $weapon)
                                                <form method="POST" action="{{ route('weapons.documents.destroy', [$weapon, $document]) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-red-600 hover:text-red-900" onclick="return confirm('¿Eliminar documento?')">
                                                        {{ __('Eliminar') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-6 text-center text-gray-500">
                                            {{ __('Sin documentos cargados.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
