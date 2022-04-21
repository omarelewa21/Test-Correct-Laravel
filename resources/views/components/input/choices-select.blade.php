@props([
    'multiple' => false,
    'options' => [],
    'withSearch' => false,
    'placeholderText' => 'ddd'
 ])

<div wire:ignore
     x-data="choices(@entangle($attributes->wire('model')),{{ $multiple ? 1 : 0 }}, @js($options), {
            allowHTML: true,
            searchEnabled: {{ $withSearch ? 1 : 0 }},
            placeholderValue: '{{ $placeholderText }}',
            searchPlaceholderValue: 'Zoek...',
            itemSelectText: '',
            removeItemButton: true,
            renderSelectedChoices: 'always',
             })"
     class="custom-choices bg-offwhite rounded-10 "
     style="min-width: 200px">
    <select x-ref="select" :multiple="multiple" placeholder="{{ $placeholderText }}"></select>
</div>