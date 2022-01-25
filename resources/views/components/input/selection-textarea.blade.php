@props([
'type' => 'cms-selection',
'editorId',
])
<div class="relative"
     x-data="selectionOptions({ value: $wire.entangle('showSelectionOptionsModal') })"
     @initwithselection.window="initWithSelection()"
>
    <x-input.rich-textarea
            wire:model.defer="{!!  $attributes->wire('model') !!}"
            editorId="{{ $editorId }}"
            type="{{ $type }}"
    />
    <div x-show="showPopup"
         @click.outside="showPopup = false"
         x-cloak
         class="absolute bg-white p-2 top-0 border border-offwhite main-shadow rounded-10"
    >
        <template x-for="element in data.elements">
            <div>
                <x-input.text x-model="element.value"/>
                <div class="inline-flex bg-off-white border border-blue-grey rounded-lg truefalse-container transition duration-150">
                    @foreach( ['true', 'false'] as $optionValue)
                        <div x-id="['text-radio']" @click="toggleChecked($event,element)">
                            <label
                                    :for="$id('text-radio')"
                                    class="bg-off-white border border-off-white rounded-lg trueFalse bold transition duration-150
                                      @if($loop->iteration == 1) true border-r-0 @else false border-l-0 @endif

                                            "
                                    :class="{'active': element.checked == '{{ $optionValue }}'}"
                            >
                                <input
                                        :id="$id('text-radio')"
                                        type="radio"
                                        class="hidden"
                                        x-model="element.checked"
                                        value="{{ $optionValue }}"
                                >
                                <span>
                                        @if ($optionValue == 'true')
                                        <x-icon.checkmark/>
                                    @else
                                        <x-icon.close/>
                                    @endif
                                    </span>
                            </label>
                        </div>
                        @if($loop->first)
                            <div class="bg-blue-grey" style="width: 1px; height: 30px; margin-top: 3px"></div>
                        @endif
                    @endforeach
                </div>
                <x-icon.trash @click="trash($event, element)"/>
            </div>
        </template>
        <div>
            <button @click="addRow()">{{ __('add extra') }}</button>
            <button></button>
        </div>
    </div>
</div>
