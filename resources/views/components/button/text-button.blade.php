@props([
'rotateIcon' => false,
'type'
])
<?php
$rotateClass = $rotateIcon ? ('rotate-svg-' . $rotateIcon) : '';
?>
@if(isset($type) && $type == 'link')
    <a {{ $attributes->merge(['class' => 'button text-button space-x-2.5 focus:outline-none '.$rotateClass]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => 'button text-button space-x-2.5 focus:outline-none '.$rotateClass]) }}>
        {{ $slot }}
    </button>
@endif

