<div {{ $attributes->wire('loading') }}
     {{ $attributes->wire('target') }}
     @notempty($attributes->get('x-show')))
        x-show="{{ $attributes->get('x-show') }}"
        x-cloak
     @endif
     class="animate-borderpulse border-6 rounded-10 @empty($attributes->get('x-show')) hidden @endif"
     style="min-height: 180px; height: 180px; animation-delay: calc({{ $delay }} * 200ms)"
>
    {{ $slot }}
</div>