@props([
'size' => 'sm',
'rotateIcon' => false,
'type',
'disabled' => false
])
<?php
$rotateClass = $rotateIcon ? ('rotate-svg-' . $rotateIcon) : '';
$size = 'button-' . $size;
?>

@if(isset($type) && $type == 'link')
    <a {{ $attributes->merge(['class' => 'button primary-button space-x-2.5 focus:outline-none ' . $rotateClass . ' ' .$size]) }}
            @disabled($disabled)
    >

        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => 'button primary-button space-x-2.5 focus:outline-none ' . $rotateClass . ' ' .$size]) }} @disabled($disabled)>
        {{ $slot }}
    </button>
@endif

