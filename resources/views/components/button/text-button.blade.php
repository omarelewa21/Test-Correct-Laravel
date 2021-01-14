@props([
    'rotateIcon' => false,
])
<?php
$rotateClass = '';
    if ($rotateIcon) {
        $rotateClass = 'rotate-svg-' . $rotateIcon;
    }
?>

<x-button class="text-button {{$rotateClass}}">
    {{ $slot }}
</x-button>
