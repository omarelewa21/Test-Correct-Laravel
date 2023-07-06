@php
    $defer = str($attributes->wire('model')?->directive)->contains('defer') ? '.defer' : '';
@endphp
<span id="{{ $containerId }}"
     @class([
         'select | inline-flex relative min-w-[150px] w-full w-auto h-10 rounded-lg border bg-offwhite items-center pl-4 pr-10 cursor-pointer group/select hover:border-primary transition-colors select-none',
         $attributes->get('class'),
         'border-bluegrey' => !$error,
         'border-allred' => $error,
     ])
     wire:ignore
     x-data="singleSelect(@js($containerId),@if($attributes->wire('model')->value) @entangle($attributes->wire('model')->value){{ $defer }} @else null @endif )"
     x-cloak
     x-on:click="toggleDropdown()"
     x-bind:class="singleSelectOpen ? 'border-primary text-primary' : ''"
     x-bind:aria-expanded="singleSelectOpen ? 'true' : 'false'"
        {{ $attributes->except(['id','class', 'wire:model']) }}
>
    <span class="selected"
          x-bind:class="value !== null ? 'text-sysbase' : 'text-midgrey italic'"
          data-select-text="{{ __('test-take.Selecteer...') }}"
          x-text="selectedText"
    ></span>
    <x-icon.chevron-small
            class="absolute right-5 transform transition-transform pointer-events-none group-hover/select:text-primary"
            x-bind:class="singleSelectOpen ? '-rotate-90'  : 'rotate-90'"
            opacity="1"
    />
    <span x-show="singleSelectOpen"
         x-transition
         x-cloak
         x-on:click.outside="closeDropdown()"
         x-on:keydown.escape.window="closeDropdown()"
         class="dropdown | absolute overflow-scroll bg-white rounded-10 z-10 py-2 flex flex-col left-0 text-sysbase"
         style="height:max-content; max-height: 315px; box-shadow: var(--popover-shadow)"
    >
        {{ $slot }}
    </span>
</span>