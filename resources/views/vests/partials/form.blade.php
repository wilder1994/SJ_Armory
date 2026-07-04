@php
    $isEdit = isset($vest);
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Cliente') }}</label>
        <select name="client_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">{{ __('Seleccione') }}</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" @selected(old('client_id', $vest->client_id ?? $clientId ?? null) == $client->id)>{{ $client->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('No. serie o código') }}</label>
        <input type="text" name="serial_number" value="{{ old('serial_number', $vest->serial_number ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Marca') }}</label>
        <input type="text" name="brand" value="{{ old('brand', $vest->brand ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Lote') }}</label>
        <input type="text" name="batch" value="{{ old('batch', $vest->batch ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Talla') }}</label>
        <input type="text" name="size" value="{{ old('size', $vest->size ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Responsable dispositivo') }}</label>
        <input type="text" name="device_responsible" value="{{ old('device_responsible', $vest->device_responsible ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Fecha fabricación') }}</label>
        <input type="date" name="manufactured_at" value="{{ old('manufactured_at', optional($vest->manufactured_at ?? null)?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Fecha vencimiento') }}</label>
        <input type="date" name="expires_at" value="{{ old('expires_at', optional($vest->expires_at ?? null)?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Trabajador') }}</label>
        <select name="worker_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">{{ __('Sin asignar') }}</option>
            @foreach ($workers as $worker)
                <option value="{{ $worker->id }}" @selected(old('worker_id', $vest->worker_id ?? null) == $worker->id)>{{ $worker->name }} @if($worker->document) ({{ $worker->document }}) @endif</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Puesto') }}</label>
        <select name="post_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">{{ __('Sin puesto') }}</option>
            @foreach ($posts as $post)
                <option value="{{ $post->id }}" @selected(old('post_id', $vest->post_id ?? null) == $post->id)>{{ $post->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">{{ __('Notas') }}</label>
        <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $vest->notes ?? '') }}</textarea>
    </div>
</div>
