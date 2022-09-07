<button {{ $attributes->merge(['class' => 'px-4 py-1.5 -mr-4 h-10 w-10 rounded-full hover:bg-primary/5 text-sysbase transition-all']) }}
        x-bind:class="{'option-menu-active !text-white hover:!text-primary': menuOpen }"
        x-data="contextMenuButton(@js($context), @js($uuid), {{ $contextDataJson }} )"
        @close-menu="closeMenu()"
        @click.stop="handle()"
>
    <x-icon.options class=""/>
</button>