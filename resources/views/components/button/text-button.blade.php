@props([
    'rotateIcon' => false,
])
<?php
$rotateClass = $rotateIcon ? ('rotate-svg-' . $rotateIcon) : '';
?>

<button {{ $attributes->merge(['class' => 'button text-button space-x-2.5 focus:outline-none '.$rotateClass]) }}>
    {{ $slot }}
</button>
