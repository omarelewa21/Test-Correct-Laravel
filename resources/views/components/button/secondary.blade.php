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

<x-button class="secondary-button button-{{$size}} {{$rotateClass}}">
    {{ $slot }}
</x-button>
