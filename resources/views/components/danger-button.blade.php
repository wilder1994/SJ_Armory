<button {{ $attributes->merge(['type' => 'submit', 'class' => 'sj-ui-btn sj-ui-btn--ghost sj-ui-btn--danger']) }}>
    {{ $slot }}
</button>
