<div x-data="{ containerId: $id('accordion'), active: null }"
     x-init="active = containerId + '-' + @js($activeContainerKey)"
     x-cloak
     class="w-full min-h-[16rem] space-y-4"
>
    {{ $slot }}
</div>