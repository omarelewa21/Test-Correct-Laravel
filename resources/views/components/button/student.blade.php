@props([
'size' => 'sm',
'rotateIcon' => false,
'disabled' => false,
])
<?php
$rotateClass = $rotateIcon ? ('rotate-svg-' . $rotateIcon) : '';
$size = 'button-' . $size;
?>

<button {{ $attributes->merge(['class' => 'button student-button space-x-2.5' . $rotateClass . ' ' .$size]) }}
        @if($disabled)
            disabled
        @endif
>
    {{ $slot }}
</button>
