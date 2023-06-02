@props([
'valueOff' => 0,
'valueOn' => 1,
'disabled' => false
])
<div @class(["border-b border-bluegrey flex w-full items-center h-[50px] gap-2.5 text-base", $attributes->get('class') ])
        {{ $attributes->except('class') }}
>
    <label @class(["switch mr-2 min-w-[var(--switch-width)]", 'disabled' => $disabled])
           x-data="{
                value: @entangle($attributes->wire('model')),
                on: @js($valueOn),
                off: @js($valueOff),
                toggleValue() {
                    this.value = this.value === this.on
                            ? this.off
                            : this.on
                }
                }"
           @if(!$disabled)
               x-on:click="toggleValue"
            @endif
    >
        <span class="slider round" :class="{'checked': value === on }"></span>
    </label>
    {{ $slot }}
</div>