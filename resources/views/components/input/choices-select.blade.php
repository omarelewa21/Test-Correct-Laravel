@props([
    'multiple' => false,
    'options' => [],
    'withSearch' => false,
    'placeholderText' => 'ddd',
    'filterContainer' => '',
 ])
<div>
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
         class="custom-choices bg-offwhite rounded-10 "
         :class="{'has-item': value.length > 0}"
         style="min-width: 200px"
         data-model-name="{{ $attributes->wire('model')->value }}"
    >
        <select x-ref="select" :multiple="multiple" placeholder="{{ $placeholderText }}"
                id="{{$attributes['id']}}"></select>
    </div>
    <template id="filter-pill-template" class="hidden">
        <div class="space-x-2" @click="$dispatch('removeFrom'+$el.dataset.filter, {value: parseInt($el.dataset.filterValue)}); $el.remove()">
            <span class="flex"></span>
            <x-icon.close-small/>{{--removeFilterItem($el)--}}
        </div>
    </template>
</div>

