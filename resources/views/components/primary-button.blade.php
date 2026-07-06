<button {{ $attributes->merge(['type' => 'submit', 'class' => 'sj-ui-btn sj-ui-btn--primary']) }}>
    {{ $slot }}
</button>
