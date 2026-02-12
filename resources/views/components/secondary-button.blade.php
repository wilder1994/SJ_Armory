<button {{ $attributes->merge(['type' => 'button', 'class' => 'sj-btn-secondary inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>




