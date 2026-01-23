<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">{{ __('Documentos') }}</h3>
            @can('update', $weapon)
                <form method="POST" action="{{ route('weapons.documents.store', $weapon) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                    @csrf
                    <input type="file" name="document" required class="text-sm">
                    <input type="date" name="valid_until" class="rounded-md border-gray-300 text-sm" placeholder="{{ __('Vence') }}">
                    <input type="text" name="observations" spellcheck="true" class="rounded-md border-gray-300 text-sm" placeholder="{{ __('Observaciones') }}">
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
                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Documento') }}</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Fecha de vencimiento') }}</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Tipo de permiso') }}</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Estado') }}</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-600">{{ __('Descargar') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($weapon->documents as $document)
                        @php
                            $days = (($document->is_permit || $document->is_renewal) && $document->valid_until)
                                ? now()->startOfDay()->diffInDays($document->valid_until, false)
                                : null;
                            $statusLabel = '-';
                            $rowClass = '';

                            if ($days !== null) {
                                if ($days <= 0) {
                                    $statusLabel = __('Vencido');
                                    $rowClass = 'bg-red-50';
                                } elseif ($days <= 90) {
                                    $statusLabel = __('Renovar permiso');
                                    $rowClass = 'bg-orange-50';
                                } elseif ($days <= 120) {
                                    $statusLabel = __('Proximo a renovar');
                                    $rowClass = 'bg-yellow-50';
                                } else {
                                    $statusLabel = __('Vigente');
                                }
                            }

                            $fileType = $document->file?->original_name
                                ? strtoupper(pathinfo($document->file->original_name, PATHINFO_EXTENSION))
                                : ($document->file?->mime_type ?? '-');
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ $document->document_name ?? __('Documento') }}</div>
                                <div class="text-xs text-gray-500">
                                    @if ($document->document_number)
                                        {{ __('Numero:') }} {{ $document->document_number }} |
                                    @endif
                                    {{ __('Tipo:') }} {{ $fileType ?: '-' }}
                                </div>
                            </td>
                            <td class="px-3 py-2">{{ optional($document->valid_until)->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-3 py-2">
                                @if ($document->permit_kind === 'porte')
                                    {{ __('Porte') }}
                                @elseif ($document->permit_kind === 'tenencia')
                                    {{ __('Tenencia') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $statusLabel }}</td>
                            <td class="px-3 py-2 text-right space-x-2">
                                <a href="{{ route('weapons.documents.download', [$weapon, $document]) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ __('Descargar') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                                {{ __('Sin documentos cargados.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
