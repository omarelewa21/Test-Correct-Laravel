@props([
    'type' => 'button',
    'color' => 'primary'
])

@php
    $buttonColor = "button-$color";
@endphp
@if($type === 'link')
    <a {{ $attributes->merge(['class' => 'new-button w-10 '.$buttonColor]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => 'new-button w-10 '.$buttonColor]) }}>
        {{ $slot }}
    </button>
@endif
