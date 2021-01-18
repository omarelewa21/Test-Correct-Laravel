@props([
    'rotateIcon' => false,
])
<?php
$rotateClass = '';
    if ($rotateIcon) {
        $rotateClass = 'rotate-svg-' . $rotateIcon;
    }
?>

<button {{ $attributes }} class="text-button {{ $rotateClass }}" >
    {{ $slot }}
</button>
