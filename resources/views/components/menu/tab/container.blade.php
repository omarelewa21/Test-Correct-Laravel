<div {{ $attributes->merge(['class' => 'border-b border-secondary sticky sticky-pseudo-bg bg-lightGrey z-1']) }}
     @if($sticky)
         style="transition: top 0.3s linear;top: 150px"
         x-on:tiles-hidden.window="$el.style.top = '100px'"
         x-on:tiles-shown.window="$el.style.top = '150px'"
     @endif
>
    <div class="w-full max-w-screen-2xl mx-auto px-10">
        <div class="flex w-full h-12.5">
            {{ $slot }}
        </div>
    </div>
</div>