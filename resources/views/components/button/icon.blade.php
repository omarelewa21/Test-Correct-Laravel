@props([
    'type' => 'button',
    'color' => 'primary'
])

@php
    $buttonColor = "button.$color";
@endphp
@if($type === 'link')
    <x-dynamic-component type="link" :component="$buttonColor" {{ $attributes->except('class') }} @class([
        $attributes->get('class'),
        'w-10 h-10 p-0',
    ])>
        {{ $slot }}
    </x-dynamic-component>
@else
    <x-dynamic-component :component="$buttonColor" {{ $attributes->except('class') }} @class([
        $attributes->get('class'),
        'w-10 h-10 p-0',
    ])>
        {{ $slot }}
    </x-dynamic-component>
@endif
