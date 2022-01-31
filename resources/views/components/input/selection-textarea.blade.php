@props([
'type' => 'cms-selection',
'editorId',
])
<div class="relative"
     x-data="selectionOptions({ value: $wire.entangle('showSelectionOptionsModal'), editorId: '{{ $editorId }}' })"
     @initwithselection.window="initWithSelection()"
>
    <x-input.rich-textarea
            wire:model.defer="{!!  $attributes->wire('model') !!}"
            editorId="{{ $editorId }}"
            type="{{ $type }}"
    />

    <div x-show="showPopup"
         @click.outside="closePopup()"
         x-cloak
         class="absolute bg-white left-1/2 -translate-x-1/2 -top-10  border border-offwhite main-shadow rounded-10"
    >
        <div class="flex items-center justify-between py-2 px-4 border-b border-secondary">
            <div class="flex items-center space-x-2.5">
                <x-icon.plus/>
                <span class="bold text-base">Selectievak toevoegen</span>
            </div>
            <div class="flex">
                <x-tooltip>
                    Dit is een selectie vraag!
                </x-tooltip>
            </div>
        </div>
        <div class="flex flex-col px-6 py-4">
            <div>
                <span class="text-base">Antwoordopties</span>
            </div>
            <template x-for="(element, key) in data.elements" :key="element.id">
                <div class="flex flex-1 space-x-2 mb-2" :id="`element${key}`">
                    <x-input.text x-model="element.value" x-bind:class="{'border-allred': hasError.empty.includes(element.id)}"/>
                    <div class="inline-flex bg-off-white border rounded-lg truefalse-container transition duration-150 pr-0.5"
                         x-bind:class="hasError.false.includes(element.id) ? 'border-allred' : 'border-blue-grey'"
                    >
                        @foreach( ['true', 'false'] as $optionValue)
                            <div x-id="['text-radio']"
                                 @click="toggleChecked($event,element)"
                                 @change="resetHasError()"
                                 :class="{'relative left-0.5': '{{ $optionValue }}' === 'false'}"
                            >
                                <label
                                        :for="$id('text-radio')"
                                        class="flex selection-toggle-label"
                                        :class="{'active': element.checked === '{{ $optionValue }}'}"
                                >
                                    <input
                                            :id="$id('text-radio')"
                                            type="radio"
                                            class="hidden"
                                            x-model="element.checked"
                                            value="{{ $optionValue }}"
                                    >
                                    <span class="flex h-full items-center px-3">
                                @if ($optionValue == 'true')
                                            <x-icon.checkmark/>
                                        @else
                                            <x-icon.close/>
                                        @endif
                                </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <button :disabled="canDelete()" :class="{'text-midgrey' : canDelete()}" @click="trash($event, element)">
                        <x-icon.trash/>
                    </button>
                </div>
            </template>
            <x-button.primary x-bind:disabled="emptyOptions()" @click="addRow()" class="justify-center">
                <x-icon.plus/>
                <span>{{ __('Optie toevoegen') }}</span>
            </x-button.primary>
        </div>

        <div class="flex items-center px-6 pb-6">
            <div class="ml-auto space-x-2">
                <x-button.text-button @click="closePopup()" size="sm">
                    {{ __('Annuleer') }}
                </x-button.text-button>
                <x-button.cta @click="save()" size="sm">
                    {{ __('Toevoegen') }}
                </x-button.cta>
            </div>
        </div>
    </div>
</div>
