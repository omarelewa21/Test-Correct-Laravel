@props([
    'current',
    'total',
    'methodCall',
    'iconName' => null,
])

<div {{ $attributes->class(['assessment-navigator | flex gap-4 items-center justify-center']) }}
     x-data="assessmentNavigator(@js($current),@js($total),@js($methodCall))"
     x-cloak
>
    <div class="flex gap-2">
        <button class="flex w-[22px] h-[22px] items-center justify-center rounded-full transition-colors"
                x-on:click="first()"
                x-bind:disabled="current === 1"
                x-bind:class="current === 1 ? 'text-white/20' : 'hover:bg-white/20' "

        >
            <x-icon.arrow-last class="inline-flex rotate-180 -top-px relative" />
        </button>
        <button class="flex w-[22px] h-[22px] items-center justify-center rounded-full transition-colors"
                x-on:click="previous()"
                x-bind:disabled="current === 1"
                x-bind:class="current === 1 ? 'text-white/20' : 'hover:bg-white/20' "
        >
            <x-icon.chevron class="inline-flex rotate-180 -top-px relative" />
        </button>
    </div>
    <div class="flex gap-1 items-center">
        <span class="py-[3px] pr-2 min-w-[30px] bold rounded-full bg-white text-sysbase text-center"
              x-bind:class="current >= 10 ? 'pl-2' : 'pl-2'"
        >
            @if($iconName)
                <x-dynamic-component component="icon.{{ $iconName }}" />
            @endif
            <span x-text="current"></span>
        </span>
        /
        <span x-text="total"></span>
    </div>
    <div class="flex gap-2">
        <button class="flex w-[22px] h-[22px] items-center justify-center rounded-full transition-colors"
                x-on:click="next()"
                x-bind:disabled="current === total"
                x-bind:class="current === total ? 'text-white/20' : 'hover:bg-white/20' "
        >
            <x-icon.chevron class="inline-flex" />
        </button>
        <button class="flex w-[22px] h-[22px] items-center justify-center rounded-full transition-colors"
                x-on:click="last()"
                x-bind:disabled="current === total"
                x-bind:class="current === total ? 'text-white/20' : 'hover:bg-white/20' "
        >
            <x-icon.arrow-last class="inline-flex" />
        </button>
    </div>
</div>