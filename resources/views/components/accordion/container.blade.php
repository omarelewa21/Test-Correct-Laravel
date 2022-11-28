<div x-data="{ containerId: $id('accordion'), active: null }"
     x-init="active = containerId + '-' + @js($activeOnInit)"
     x-cloak
     class="mx-auto max-w-4xl w-full min-h-[16rem] space-y-4"
>
    {{ $slot }}
</div>