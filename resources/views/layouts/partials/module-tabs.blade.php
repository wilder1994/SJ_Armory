@php
    $tabs = $moduleNav['tabs'] ?? [];
@endphp

@if ($tabs !== [])
    <nav class="sj-module-tabs" aria-label="{{ $moduleNav['active_module'] }}">
        @foreach ($tabs as $tab)
            @php
                $isDisabled = (bool) ($tab['disabled'] ?? false);
                $isActive = (bool) ($tab['active'] ?? false);
            @endphp

            @if ($isDisabled)
                <span class="sj-module-tab is-disabled" title="{{ $tab['badge'] }}">
                    <span class="sj-module-tab__hit">
                        {{ $tab['label'] }}
                        @if ($tab['badge'])
                            <span class="sj-sidebar__badge">{{ $tab['badge'] }}</span>
                        @endif
                    </span>
                </span>
            @else
                <a
                    href="{{ $tab['url'] }}"
                    @class(['sj-module-tab', 'is-active' => $isActive])
                    @if ($isActive) aria-current="page" @endif
                >
                    <span class="sj-module-tab__hit">{{ $tab['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>
@endif
