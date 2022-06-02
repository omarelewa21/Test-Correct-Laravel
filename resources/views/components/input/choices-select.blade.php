@props([
    'multiple' => false,
    'options' => [],
    'withSearch' => false,
    'placeholderText' => 'ddd',
    'filterContainer' => '',
 ])
<div>
    <div wire:ignore
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
        <div class="space-x-2">
            <span class="flex"></span>
            <x-icon.close-small @click="removeFilterItem($el)"/>
        </div>
    </template>
</div>

