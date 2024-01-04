@props([
    'size' => 'md',
    'context',
    'uuid',
    'contextDataJson',
    'preventLivewireCall' => false,
    'disabled' => false,
])
<button {{ $attributes->except('class') }}
        @class([
            "flex items-center justify-center rounded-full text-sysbase transition-all relative",
            "h-10 min-w-[2.5rem] w-10 -top-3 -mr-4" => $size === 'md',
            "h-[30px] w-[30px]" => $size !== 'md',
            "hover:bg-primary/5" => !$disabled,
            "pointer-events-none" => $disabled,
            $attributes->get('class')
        ])
        x-bind:class="{'option-menu-active !text-white hover:!text-primary': menuOpen ?? null }"
        x-data="contextMenuButton(@js($context), @js($uuid), {{ $contextDataJson ?? '{}' }}, @js($preventLivewireCall))"
        x-on:close-menu="closeMenu()"
        x-on:click.stop="handle()"
        @disabled($disabled)
>
    <x-icon.options class=""/>
</button>