@props([
    'wireModel' => null,
    'disabled' => false,
    'initialStatus' => null,
    'toggleValue' => 0,
    'identifier' => null,
])
<x-button.slider
        :wire:model="$wireModel"
        button-width="40px"
        :options="['1' => 'yes', '0' =>  'no', '0.5' => 'half']"
        :useNamedSlots="true"
        :disabled="$disabled"
        :disabledStyling="$disabled"
        :initialStatus="$initialStatus"
        :toggleValue="$toggleValue"
        :identifier="$identifier"
>
    <x-slot name="yes">
        <x-icon.checkmark/>
    </x-slot>
    <x-slot name="no">
        <x-icon.close/>
    </x-slot>
    <x-slot name="half">
        <x-icon.half-points/>
    </x-slot>
</x-button.slider>