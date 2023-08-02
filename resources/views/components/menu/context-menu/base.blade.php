@props(['context','class' => 'w-50'])
<div id="context-menu-base"
     @class(['absolute bg-white py-2 main-shadow rounded-10 z-1', $class])
     x-data="contextMenuHandler()"
     x-show="contextMenuOpen"
     x-effect="setTimeout(() => {
        preventMenuFallOffScreen();
    }, 10);"
     x-transition:enter="transition ease-out origin-top-right duration-200"
     x-transition:enter-start="opacity-0 transform scale-90"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition origin-top-right ease-in duration-100"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-90"
     x-on:{{ $context }}-context-menu-show.window="handleIncomingEvent($event.detail); "
     x-on:{{ $context }}-context-menu-close.window="closeMenu(); "
     @click.outside="closeMenu()"
     @click="closeMenu()"
     x-cloak
>
    {{ $slot }}
</div>