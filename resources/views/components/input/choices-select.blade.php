@props([
    'multiple' => false,
    'options' => [],
    'withSearch' => false,
    'placeholderText' => 'ddd',
    'filterContainer' => '',
    'hasErrors' => false,
 ])
<div class="{{ $hasErrors ? 'has-error' : ''  }}">
    <div wire:ignore
         {{ $attributes->wire('key') ? 'wire:key="'. $attributes->wire('key')->value. '"' : '' }}
         x-data="choices(@entangle($attributes->wire('model')),
                    {{ $multiple ? 1 : 0 }},
                    @js($options),
                    {
                        allowHTML: true,
                        searchEnabled: {{ $withSearch ? 1 : 0 }},
                        placeholderValue: '{{ $placeholderText }}',
                        searchPlaceholderValue: '{{ __('Zoek') }}...',
                        itemSelectText: '',
                        removeItemButton: true,
                        renderSelectedChoices: 'always',
                        resetScrollPosition: false,
                        fuseOptions:{
                            treshold:0.3
                        }
                    },
                    '{{ $filterContainer }}'
             )"
         class="custom-choices bg-offwhite rounded-10 relative"
         :class="{'has-item': value.length > 0}"
         style=""
         data-model-name="{{ $attributes->wire('model')->value }}"
    >
        <select x-ref="select" :multiple="multiple" placeholder="{{ $placeholderText }}"
                id="{{$attributes['id']}}"></select>
        <x-icon.chevron-small class="absolute right-4 top-1/2 -translate-y-1/2 rotate-90 pointer-events-none" opacity="1"/>
    </div>
    <template id="filter-pill-template" class="hidden">
        <button class="space-x-2" @click="/*if(typeof filterloading === null || !filterLoading) {*/ $dispatch('removeFrom'+$el.dataset.filter, {value: parseInt($el.dataset.filterValue)}); $el.remove() /*}*/">
            <span class="flex"></span>
            <x-icon.close-small/>
        </button>
    </template>
</div>

