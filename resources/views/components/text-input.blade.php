@props(['disabled' => false])

@php
    $type = $attributes->get('type', 'text');
    $spellcheck = in_array($type, ['text', 'search'], true) ? 'true' : 'false';
@endphp

<input {{ $disabled ? 'disabled' : '' }} spellcheck="{{ $spellcheck }}" {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) !!}>




