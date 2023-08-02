@props(['withTileEvents' => false, 'maxWidthClass' => 'max-w-screen-2xl px-10'])

<div x-ref="tab-container"
     @class([
      'border-b border-secondary sticky z-1',
      $attributes->get('class'),
      'sticky-pseudo-bg' => $withTileEvents,
      'top-0' => !$withTileEvents,
      ])
     @if($withTileEvents)
         style="transition: top 0.3s linear;top: 150px"
         x-on:tiles-hidden.window="$el.style.top = '100px'"
         x-on:tiles-shown.window="$el.style.top = '150px'"
     @endif
        {{ $attributes->except('class') }}
>
    <div @class(["w-full mx-auto", $maxWidthClass ])>
        <div class="flex w-full h-12.5">
            {{ $slot }}
        </div>
    </div>
</div>