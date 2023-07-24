@props([
'size' => 'sm',
'rotateIcon' => false,
'selid' => '',
'type' => 'button',
'disabled' => false,
'componentName' => 'primary-button',
'withBackgroundGradient' => true,
])

<?php
$rotateClass = $rotateIcon ? ('rotate-svg-' . $rotateIcon) : '';
$size = 'button-' . $size;
?>

@if($type === 'link') <a
@else                 <button
@endif
            @if($selid) selid="{{ $selid }}" @endif
            {{ $attributes->except('class') }}
            @class([
                $attributes->get('class'),
                'button space-x-2.5',
                $rotateClass,
                $size,
                $componentName,
                'button-gradient' => $withBackgroundGradient,
            ])
            @disabled($disabled)
    >
        {{ $slot }}
@if($type === 'link') </a>
@else                 </button>
@endif
