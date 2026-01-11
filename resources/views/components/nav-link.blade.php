@props(['active'])

@php
$classes = ($active ?? false)
            ? 'sj-active inline-flex items-center px-1 pt-1 border-b-2 text-sm font-semibold leading-5 focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-200 hover:text-white hover:border-white/30 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
