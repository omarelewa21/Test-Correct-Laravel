{{--
-- Important note:
--
-- This template is based on an example from Tailwind UI, and is used here with permission from Tailwind Labs
-- for educational purposes only. Please do not use this template in your own projects without purchasing a
-- Tailwind UI license, or they’ll have to tighten up the licensing and you’ll ruin the fun for everyone.
--
-- Purchase here: https://tailwindui.com/
--}}

@props([
    'label' => '',
    'button' => 'dropdown-button',
    'labelstyle' => '',
    'dropdownwidth' => null,
    'chevron' => true,
])

<div x-data="{ open: false }" @keydown.window.escape="open = false" @click.outside="open = false"
     class="relative inline-block text-left z-10" style="{{$attributes->get('style')}}">
    <div>
        <span class="rounded-md">
            <button @click="open = !open" type="button"
                    class="dropdown-button-class text-button button-md rotate-svg-90 focus:shadow-outline-blue {{$button}} {{ $labelstyle }} {{ $attributes->get('class') }}"
                    :class="{primary: open}"
                    id="options-menu" aria-haspopup="true" x-bind:aria-expanded="open" aria-expanded="true">
                <span class="align-middle ">{{ $label }}</span>
                @if($chevron)
                    <x-icon.chevron></x-icon.chevron>
                @endif
            </button>
        </span>
    </div>

    <div x-show="open" style="display: none; {{ $dropdownwidth ? 'width: '.$dropdownwidth : '' }}" x-description="Dropdown panel, show/hide based on dropdown state."
         x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="origin-top-right absolute z-40 bg-white right-0 w-56 rounded-10 shadow-lg py-2.5 overflow-hidden main-shadow">
        <div class="rounded-md bg-white shadow-xs">
            <div class="dropdown-items-container" role="menu" aria-orientation="vertical"
                 aria-labelledby="options-menu">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
