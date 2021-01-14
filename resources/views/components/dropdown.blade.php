{{--
-- Important note:
--
-- This template is based on an example from Tailwind UI, and is used here with permission from Tailwind Labs
-- for educational purposes only. Please do not use this template in your own projects without purchasing a
-- Tailwind UI license, or they’ll have to tighten up the licensing and you’ll ruin the fun for everyone.
--
-- Purchase here: https://tailwindui.com/
--}}

@props(['label' => ''])

<div x-data="{ open: false }" @keydown.window.escape="open = false" @click.away="open = false" class="relative inline-block text-left z-10">
    <div>
        <span class="rounded-md shadow-sm">
            <button @click="open = !open" type="button" class="body1 bold rotate-svg-90 space-x-1.5 inline dropdown-button justify-center w-full rounded-md px-4 py-2 bg-white text-sm focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-50 active:text-gray-800 transition ease-in-out duration-150" id="options-menu" aria-haspopup="true" x-bind:aria-expanded="open" aria-expanded="true">
                <span class="align-middle">{{ $label }}</span>
                <x-icon.chevron></x-icon.chevron>
            </button>
        </span>
    </div>

    <div x-show="open" style="display: none;" x-description="Dropdown panel, show/hide based on dropdown state." x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg">
        <div class="rounded-md bg-white shadow-xs">
            <div class="dropdown-items-container" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
