@props([
    'title' => ''
])

<button title="{{$title}}"
        {{ $attributes->merge(['class' => 'flex items-center justify-center min-w-[40px] w-10 h-10 rounded-full hover:bg-[#F2F6FF] hover:text-primary transition-colors shadow-icon-cirle focus-visible:outine-0']) }}
>
    {{ $slot }}
</button>