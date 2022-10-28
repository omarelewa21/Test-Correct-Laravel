@props([
    'wireModel',
    'disabled' => false,
])
<x-button.slider wire:model="{{ $wireModel }}"
                 button-width="45px"
                 :options="['1' => 'yes', '0' =>  'no']"
                 :useNamedSlots="true"
                 :disabled="$disabled"
                 :disabledStyling="$disabled"
>
    <x-slot name="yes">
        <x-icon.checkmark/>
    </x-slot>
    <x-slot name="no">
        <x-icon.close/>
    </x-slot>
</x-button.slider>