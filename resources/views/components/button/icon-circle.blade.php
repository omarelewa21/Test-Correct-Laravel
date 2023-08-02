@props([
    'title' => ''
])

<button title="{{$title}}"
        {{ $attributes->merge(['class' => '
    icon-circle-button
    ']) }}
>
    {{ $slot }}
</button>