@props([
'size' => 'md',
'rotateIcon' => false,
'type',
'withHover' => false,
'disabled' => false,
])
<?php
$rotateClass = $rotateIcon ? ('rotate-svg-' . $rotateIcon) : '';
$size = 'button-' . $size;
$withHover = $withHover ? 'text-button-hover-bg' : '';
?>
@if(isset($type) && $type == 'link')
    <a {{ $attributes->merge(['class' => 'button text-button space-x-2.5 '.$rotateClass . ' ' . $size . ' ' . $withHover]) }}
       @if($disabled)
            disabled
       @endif
    >
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => 'button text-button space-x-2.5 '.$rotateClass . ' ' .$size . ' ' . $withHover]) }}
        @if($disabled)
            disabled
        @endif
    >
        {{ $slot }}
    </button>
@endif

