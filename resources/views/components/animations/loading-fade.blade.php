@props(['loadProperty', 'color' => 'grey'])
<div {{ $attributes->except('class') }} @class(["absolute inset-0 flex items-center", $attributes->get('class')])
     x-show="{{ $loadProperty }}"
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-bind:class="{'z-10': {{ $loadProperty }}}"
     wire:ignore
>
    @if($slot->toHtml() === '')
        <x-knightrider :color="$color"/>
    @else
        {{ $slot }}
    @endif
</div>