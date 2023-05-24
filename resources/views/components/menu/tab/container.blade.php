@props(['withTileEvents' => false, 'maxWidthClass' => 'max-w-screen-2xl px-10'])
@php
    $stickyClasses = $withTileEvents ? 'sticky-pseudo-bg' : 'top-0';
@endphp
<div {{ $attributes->merge(['class' => 'border-b border-secondary sticky bg-lightGrey z-1 ' . $stickyClasses]) }}
     @if($withTileEvents)
         style="transition: top 0.3s linear;top: 150px"
         x-on:tiles-hidden.window="$el.style.top = '100px'"
         x-on:tiles-shown.window="$el.style.top = '150px'"
     @endif
>
    <div @class(["w-full mx-auto", $maxWidthClass ])>
        <div class="flex w-full h-12.5">
            {{ $slot }}
        </div>
    </div>
</div>