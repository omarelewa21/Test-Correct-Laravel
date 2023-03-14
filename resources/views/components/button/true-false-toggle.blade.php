@props([
    'wireModel' => null,
    'disabled' => false,
    'initialValue' => null,
])
<x-button.slider
                 :wire:model="$wireModel"
                 button-width="40px"
                 :options="['1' => 'yes', '0' =>  'no']"
                 :useNamedSlots="true"
                 :disabled="$disabled"
                 :disabledStyling="$disabled"
                 :initialValue="$initialValue"
>
    <x-slot name="yes">
        <x-icon.checkmark />
    </x-slot>
    <x-slot name="no">
        <x-icon.close />
    </x-slot>
</x-button.slider>