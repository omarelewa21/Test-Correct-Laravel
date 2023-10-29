@props([
    'current',
    'total',
    'methodCall',
    'iconName' => null,
    'last' => $total,
    'first' => 1,
])

<div {{ $attributes->class(['assessment-navigator | flex gap-4 items-center justify-center']) }}
     x-data="assessmentNavigator(@js((int)$current),@js($total),@js($methodCall), @js($last), @js($first))"
     x-cloak
     x-on:update-navigator="updateProperties($event.detail)"
     x-on:continue-navigation="Alpine.$data($el)[$event.detail.method]()"
     wire:ignore
>
    <div class="flex gap-3">
        <x-button.text :white="true"
                       class="first |"
                       size="sm"
                       x-on:click="first()"
                       x-bind:disabled="current === firstValue"
        >
            <x-icon.arrow-last class="inline-flex rotate-180 -top-0 relative"/>
        </x-button.text>
        <x-button.text :white="true"
                       class="previous |"
                       size="sm"
                       x-on:click="previous()"
                       x-bind:disabled="current === firstValue"
                       id="btn_{{ $methodCall }}_previous"
        >
            <x-icon.chevron class="inline-flex rotate-180 -top-0 relative"/>
        </x-button.text>
    </div>
    <div class="flex gap-1 items-center">
        <span class="inline-flex items-center justify-center gap-0.5 py-[3px] pr-2 min-w-[30px] bold rounded-full bg-white text-sysbase text-center"
              x-bind:class="current >= 10 ? 'pl-2' : 'pl-2'"
        >
            @if($iconName)
                <x-dynamic-component component="icon.{{ $iconName }}"/>
            @endif
            <span class="inline-flex" x-text="current"></span>
        </span>
        <span class="inline-flex">/</span>
        <span class="inline-flex" x-text="total"></span>
    </div>
    <div class="flex gap-3">
        <x-button.text :white="true"
                       class="next |"
                       size="sm"
                       x-on:click="next()"
                       x-bind:disabled="current === lastValue"
                       id="btn_{{ $methodCall }}_next"
        >
            <x-icon.chevron class="inline-flex top-0 relative"/>
        </x-button.text>
        <x-button.text :white="true"
                       class="last |"
                       size="sm"
                       x-on:click="last()"
                       x-bind:disabled="current === lastValue"
        >
            <x-icon.arrow-last class="inline-flex top-0 relative"/>
        </x-button.text>
    </div>
</div>