<div @class([
      "flex px-6 py-2.5 space-x-2.5 cursor-pointer transition hover:text-primary hover:bg-primary/5 active:bg-primary/10",
      $attributes->get('class')
    ])
    {{ $attributes->except('class') }}
>
    <x-icon.plus-in-circle/>
    @isset($subtext)
        <div class="flex flex-col ">
            <button class="bold mt-px text-left">{{ $slot }}</button>
            <span class="text-sm note regular">{{ $subtext }}</span>
        </div>
    @else
        <button class="bold mt-px">{{ $slot }}</button>
    @endisset
</div>