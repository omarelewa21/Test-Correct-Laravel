@props([
    'multiple' => false,
    'options' => [],
    'withSearch' => false,
    'placeholderText' => '',
    'filterContainer' => '',
    'hasErrors' => false,
    'searchPlaceholder' => __('cms.Search...'),
    'sortOptions' => true,
    'initWidth' => 100,
 ])
<div class="{{ $hasErrors ? 'has-error' : ''  }}">
    <div wire:ignore x-cloak
         {{ $attributes->wire('key') }}
         x-data="choices(@entangle($attributes->wire('model')),
                    {{ $multiple ? 1 : 0 }},
                    @js($options),
                    {
                        allowHTML: true,
                        searchEnabled: {{ $withSearch ? 1 : 0 }},
                        placeholderValue: @js($placeholderText),
                        searchPlaceholderValue: @js($searchPlaceholder),
                        itemSelectText: '',
                        removeItemButton: {{ $multiple ? 1 : 0 }},
                        renderSelectedChoices: 'always',
                        resetScrollPosition: false,
                        fuseOptions:{
                            treshold:0.3
                        },
                        shouldSort: @js($sortOptions),
                        position: 'bottom'
                    },
                    '{{ $filterContainer }}',
                    '{{ $initWidth }}'
             )"
         class="{{ $attributes->get('class') }} custom-choices bg-offwhite rounded-10 relative"
         @if($multiple)
         :class="{'has-item': value.length > 0}"
         @endif
         data-model-name="{{ $attributes->wire('model')->value }}"
         x-on:mouseenter="$el.querySelector('svg').classList.add('primary');"
         x-on:mouseleave="$el.querySelector('svg').classList.remove('primary');"
         x-on:reset-width="handleContainerWidth"
         {{ $attributes->except(['class', 'wire:model', 'wire:key', 'id']) }}
    >
        <select x-ref="select" :multiple="multiple" placeholder="{{ $placeholderText }}"
                id="{{$attributes['id']}}">
        </select>
        <x-icon.chevron-small x-ref="chevron" class="choices-select-chevron absolute right-4 top-1/2 -translate-y-1/2 rotate-90 pointer-events-none" opacity="1"/>
        <span id="text-length-helper" class="invisible flex relative -z-10 h-0 w-fit">{{ $placeholderText }}</span>
    </div>
    <template id="filter-pill-template" class="hidden">
        <button class="space-x-2"
                @click="$dispatch( $el.dataset.removeEventName, { value: $el.dataset.filterValue } ); $el.remove(); "
                data-trans-any="{{ __('cms.Alle') }}"
        >
            <span class="flex"></span>
            <x-icon.close-small/>
        </button>
    </template>
</div>

