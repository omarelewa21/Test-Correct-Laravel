@props([
    'size' => 'md',
    'context',
    'uuid',
    'contextDataJson',
    'preventLivewireCall' => false,
])
<button {{ $attributes->merge([
    'class' => 'flex items-center justify-center rounded-full hover:bg-primary/5 text-sysbase transition-all relative  ' . ($size === 'md' ? 'h-10 w-10 -top-3 -mr-4' : 'h-[30px] w-[30px]')
    ]) }}
        x-bind:class="{'option-menu-active !text-white hover:!text-primary': menuOpen ?? null }"
        x-data="contextMenuButton(@js($context), @js($uuid), {{ $contextDataJson ?? '{}' }}, @js($preventLivewireCall))"
        @close-menu="closeMenu()"
        @click.stop="handle()"
>
    <x-icon.options class=""/>
</button>