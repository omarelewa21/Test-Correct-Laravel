<div x-data="{ containerId: $id('accordion'), active: null }"
     x-init="active = containerId + '-' + @js($activeContainerKey)"
     x-cloak
     x-on:set-active-block="active = containerId + '-' + $event.detail.activeBlockId "
     {{ $attributes->merge(['class' => 'w-full space-y-4']) }}
>
    {{ $slot }}
</div>