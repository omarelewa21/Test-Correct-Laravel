<div x-data="multiDropdownSelect(@js($options))"
     x-cloak
     class="relative"
     wire:ignore
>
    <div class="bg-white rounded-10 p-4"
         x-on:click="open = !open"
    >
        <input type="text" x-model="query" placeholder="{{ $title }}" x-on:click.stop="if(!open) { open = !open }">
    </div>

    <div x-show="open"
         x-on:click.outside="open = false"
         class="absolute overflow-scroll bg-white rounded-10 z-10 py-2 "
         style="min-width: 300px; max-height: 315px; box-shadow: var(--popover-shadow)"
    >
        <template x-for="option in options">
            <div class="parent" x-bind:data-id="option.value">
                <div class="flex w-full justify-between items-center pl-6 pr-4"
                     x-on:click="subToggle(option.value)"
                >
                    <div class="flex py-3 gap-2 items-center"
                         x-on:click.prevent.stop="parentToggle($el, option.value)"
                    >
                        <x-input.checkbox />
                        <span x-text="option.label"></span>
                        <span x-text="option.children.length"></span>
                    </div>
                    <x-icon.chevron />
                </div>

                <div x-show="openSubs.includes(option.value)">
                    <template x-for="child in option.children">
                        <div class="flex gap-2 py-3 w-full ml-14 items-center"
                             x-on:click.prevent.stop="childToggle($el, child.value)"
                             x-bind:data-id="child.value"
                             x-bind:data-parent-id="child.customProperties.parentId"
                        >
                            <x-input.checkbox />
                            <span x-text="child.label"></span>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>