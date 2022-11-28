@props([
'size' => 'sm',
'rotateIcon' => false,
'selid' => '',
'type',
'disabled' => false
])
<?php
$rotateClass = $rotateIcon ? ('rotate-svg-' . $rotateIcon) : '';
$size = 'button-' . $size;
?>
@if(isset($type) && $type == 'link')
    <a selid="{{ $selid }}" {{ $attributes->merge(['class' => 'button cta-button space-x-2.5 focus:outline-none ' . $rotateClass . ' ' .$size]) }}
    @if($disabled) disabled @endif
    >
        {{ $slot }}
    </a>
@else
    <button selid="{{ $selid }}"
            {{ $attributes->merge(['class' => 'button cta-button space-x-2.5 focus:outline-none ' . $rotateClass . ' ' .$size]) }}
            @if($disabled) disabled @endif
    >
        {{ $slot }}
    </button>
@endif
