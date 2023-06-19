<div x-data="multiDropdownSelect(@js($options), @js($containerId), @js($attributes->wire('model')), @js($itemLabels))"
     x-cloak
     @class(['relative', $attributes->get('class')])
     wire:ignore
     {{ $attributes->except('class') }}
     x-on:remove-item="removeFilterPill($event.detail)"
>
    @if($label)
        <span class="transition-all" x-bind:class="{'text-primary bold': searchFocussed || open}">{{ $label }}</span>
    @endif
    <div class="flex relative items-center w-fit hover:text-primary cursor-pointer"
         x-on:click="toggleDropdown()"
    >
        <input type="text"
               placeholder="{{ $title }}"
               class="h-10 pl-4 pr-8 cursor-pointer placeholder-sysbase border rounded-10 focus:placeholder-primary focus:bg-primary/5 focus:outline-none hover:border-primary"
               x-model="query"
               x-on:click.stop="if(!open) openDropdown()"
               x-bind:class="{
                'bg-offwhite border-primary placeholder-primary hover:placeholder-sysbase' :  open,
                'bg-offwhite border-bluegrey': !open,
                'bg-primary/5 border-primary': !open && (checkedParents.length !== 0 || checkedChildren.length !== 0)
               }"
               x-on:focus="searchFocussed = true;"
               x-on:blur="searchFocussed = false;"
        >
        <x-icon.chevron-small class="absolute right-5 transform transition-transform pointer-events-none"
                              x-bind:class="open ? 'rotate-90'  : '-rotate-90'"
                              opacity="1"
        />
    </div>

    <div x-show="open"
         x-transition
         x-on:click.outside="closeDropdown()"
         class="dropdown | absolute overflow-scroll bg-white rounded-10 z-10 py-2 "
         style="min-width: 300px;height:max-content; max-height: 315px; box-shadow: var(--popover-shadow)"
    >
        <template x-for="option in options">
            <div class="parent option flex-col flex cursor-pointer bold"
                 x-bind:data-id="option.value"
                 x-bind:data-parent-id="option.value"
            >
                <div class="flex w-full justify-between items-center pl-6 pr-4 "
                     x-on:click="if(!parentDisabled(option)) subClick(option.value)"
                     x-bind:class="parentDisabled(option) ? 'opacity-50' : 'hover:text-primary hover:bg-primary/5 active:bg-primary/10'"
                     x-bind:title="parentDisabled(option) ? labels.parent_disabled : 'Open'"
                >
                    <div class="flex py-3 gap-2 items-center"
                         x-on:click.prevent.stop="if(!parentDisabled(option)) parentClick($el, option)"
                    >
                        <div class="relative isolate"
                             x-bind:class="{'checkbox-disabled': parentDisabled(option)}"
                        >
                            <x-input.checkbox />
                            <span x-show="parentPartiallyToggled(option)"
                                  class="checkbox-container absolute top-0 left-0"
                            >
                                <span class="checkmark !bg-primary !border-primary">
                                    <x-icon.min class="!inline-flex !w-4 !h-4" />
                                    </span>
                            </span>
                        </div>
                        <span x-text="option.label"></span>
                        <span x-text="option.children.length"></span>
                    </div>
                    <x-icon.chevron class="transform transition-transform"
                                    x-bind:class="{'rotate-90': openSubs.includes(option.value)}"
                    />
                </div>

                <div x-show="openSubs.includes(option.value)" x-collapse.duration.150ms>
                    <template x-for="child in option.children">
                        <div class="child option | flex gap-2 py-3 w-full pl-14 items-center"
                             x-on:click.prevent.stop="if(child.disabled !== true) childClick($el, child)"
                             x-bind:data-id="child.value"
                             x-bind:data-parent-id="child.customProperties.parentId"
                             x-bind:data-disabled="child.disabled === true ? 'true' : 'false'"
                             x-bind:class="child.disabled === true ? 'opacity-50 checkbox-disabled' : 'hover:text-primary hover:bg-primary/5 active:bg-primary/10'"
                             x-bind:title="child.disabled === true ? labels.child_disabled : child.label"
                        >
                            <x-input.checkbox/>
                            <span x-text="child.label"></span>
                        </div>
                    </template>
                </div>
            </div>
        </template>
        <template x-if="searchEmpty">
            <span class="px-6 italic text-sm">Geen resultaten</span>
        </template>
    </div>

    <template id="filter-pill-template" class="hidden">
        <button class="space-x-2"
                x-on:click="$el.selectComponent.dispatchEvent(new CustomEvent('remove-item', {detail: {item:$el.item, element: $el}}))"
                data-trans-any="{{ __('cms.Alle') }}"
        >
            <span class="flex"></span>
            <x-icon.close-small/>
        </button>
    </template>
</div>