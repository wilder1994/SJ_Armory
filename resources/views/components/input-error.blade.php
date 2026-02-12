@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'm-0 list-none p-0 space-y-1 text-sm text-red-600']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
