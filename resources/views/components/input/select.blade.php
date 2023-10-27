@php
    $defer = str($attributes->wire('model')?->directive)->contains('defer') ? '.defer' : '';
@endphp
<span id="{{ $containerId }}"
     @class([
         'single-select | inline-flex relative min-w-[150px] w-full w-auto h-10 rounded-lg border bg-offwhite items-center pl-4 pr-10 cursor-pointer group/select hover:border-primary transition-colors select-none',
         $attributes->get('class'),
         'border-bluegrey' => !$error,
         'border-allred' => $error,
     ])
     wire:ignore
     x-data="singleSelect(@js($containerId),@if($attributes->wire('model')->value) @entangle($attributes->wire('model')->value){{ $defer }} @else null @endif , @js($disabled))"
     x-cloak
     x-on:click="toggleDropdown()"
     x-on:select-option=""
     x-on:disable-single-select="disableDropdown()"
     x-on:enable-single-select="enableDropdown()"
     x-bind:class="{'border-primary text-primary': singleSelectOpen, 'disabled pointer-events-none bg-white border-lightGrey text-midgrey': singleSelectDisabled }"
     x-bind:aria-expanded="singleSelectOpen ? 'true' : 'false'"
        {{ $attributes->except(['id','class', 'wire:model']) }}
>
    <span class="selected truncate font-normal"
          x-bind:class="['', null].includes(value) ? 'text-midgrey italic' : 'text-sysbase'"
          x-bind:title="selectedText"
          x-text="selectedText"
          data-select-text="{{ $placeholder }}"
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
         class="dropdown | absolute overflow-scroll bg-white rounded-10 z-10 py-2 flex flex-col left-0 text-sysbase w-max"
         style="height:max-content; max-height: 315px; box-shadow: var(--popover-shadow); z-index: 100;"
    >
        @if($emptyOption)
            <x-input.option :label="$placeholder" placeholder/>
        @endif
        {{ $slot }}
    </span>
</span>