@props([
    'size' => 'sm',
    'rotateIcon' => false,
])
<?php
    $rotateClass = '';
    if ($rotateIcon) {
        $rotateClass = 'rotate-svg-' . $rotateIcon;
    }
?>

<x-button class="primary-button button-{{$size}} {{$rotateClass}}">
    {{ $slot }}
</x-button>
