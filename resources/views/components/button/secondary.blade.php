@props([
'size' => 'sm',
'rotateIcon' => false,
'disabled' => false,
])
<?php
$rotateClass = $rotateIcon ? ('rotate-svg-' . $rotateIcon) : '';
$size = 'button-' . $size;
?>

<button {{ $attributes->merge(['class' => 'button secondary-button space-x-2.5 focus:outline-none ' . $rotateClass . ' ' .$size]) }}
        @if($disabled)
            disabled
        @endif
>
    {{ $slot }}
</button>
