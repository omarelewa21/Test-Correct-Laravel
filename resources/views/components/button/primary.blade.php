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
    <a {{ $attributes->merge(['class' => 'button primary-button space-x-2.5 focus:outline-none ' . $rotateClass . ' ' .$size]) }}>

        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => 'button primary-button space-x-2.5 focus:outline-none ' . $rotateClass . ' ' .$size]) }} @if($disabled) disabled @endif>

        {{ $slot }}
    </button>
@endif

