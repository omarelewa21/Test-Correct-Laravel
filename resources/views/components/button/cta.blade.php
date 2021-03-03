@props([
'size' => 'sm',
'rotateIcon' => false,
])
<?php
$rotateClass = $rotateIcon ? ('rotate-svg-' . $rotateIcon) : '';
$size = 'button-' . $size;
?>

<button {{ $attributes->merge(['class' => 'button cta-button space-x-2.5 focus:outline-none ' . $rotateClass . ' ' .$size]) }}>
    {{ $slot }}
</button>
