@props(['activeTooltipIconClasses' => ''])
<div wire:ignore x-data="tooltip(@js($alwaysLeft), @js($positionTop))"
     @class([
       $attributes->get('class'),
       'tooltip-container relative flex items-center justify-center rounded-full transition-colors cursor-pointer invisible',
       'w-[22px]' => !$iconWidth,
       'h-[22px]' => !$iconHeight,
     ])
     x-cloak
     x-bind:class="tooltip ? @js($activeClasses) : @js($idleClasses)"
     x-on:click="tooltip = !tooltip"
     x-on:click.outside="tooltip = false"
     x-on:scroll.window="handleScroll()"
     x-on:resize.window="handleResize()"
     x-on:close="tooltip = false"
>
    @if($idleIcon)
        {{ $idleIcon }}
    @else
        <x-icon.questionmark-small x-show="!tooltip" x-cloak />
    @endif
    <x-icon.close-small x-show="tooltip" x-cloak  class="{{ $activeTooltipIconClasses }}"/>
    <div x-show="tooltip"
         x-ref="tooltipdiv"
         @class([
             $tooltipClasses,
             "fixed max-w-sm w-max bg-off-white rounded-10 p-6 main-shadow z-50 flex top-8 left-1/2 -translate-x-1/2 text-sysbase cursor-default invisible",
         ])
         x-on:click.stop=""
    >
        {{ $slot }}
    </div>
</div>