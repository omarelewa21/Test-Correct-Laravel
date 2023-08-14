@props([
'type' => 'cms-selection',
'editorId',
'disabled' => false,
'lang' => 'nl_NL',
'allowWsc' => false,
])
<div class="relative"
     x-data="selectionOptions({ value: $wire.entangle('showSelectionOptionsModal'), editorId: '{{ $editorId }}' })"
     @initwithselection.window="initWithSelection()"
>
    <x-input.rich-textarea
            wire:model.defer="{!!  $attributes->wire('model') !!}"
            editorId="{{ $editorId }}"
            type="{{ $type }}"
            :disabled="$disabled"
            lang="{{ $lang }}"
            :allowWsc="$allowWsc"
    />

    <div x-show="showPopup"
         @click.outside="closePopup()"
         x-cloak
         class="absolute left-1/2 -translate-x-1/2  z-[101]"
         :style="`top: -${data.elements.length*2}rem`"
    >
        <div x-show="showPopup" x-cloak class="border border-offwhite main-shadow rounded-10 bg-white">
            <div class="flex items-center justify-between py-2 px-4 border-b border-secondary">
                <div class="flex items-center space-x-2.5">
                    <x-icon.plus/>
                    <span class="bold text-base">{{ __('cms.Selectievak toevoegen') }}</span>
                </div>
{{--                <div class="flex">--}}
{{--                    <x-tooltip>--}}
{{--                        Dit is een selectie vraag!--}}
{{--                    </x-tooltip>--}}
{{--                </div>--}}
            </div>
            <div class="flex flex-col px-6 py-4">
                <div>
                    <span class="text-base">{{ __('cms.Antwoordopties') }}</span>
                </div>
                <template x-for="(element, key) in data.elements" :key="element.id">
                    <div class="flex flex-1 space-x-2 mb-2" :id="`element${key}`">
                        <x-input.text x-model="element.value"
                                      x-bind:class="{'border-allred': hasError.empty.includes(element.id)}"/>
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
                        <button :disabled="canDelete()" :class="{'text-midgrey' : canDelete()}"
                                @click="trash($event, element)">
                            <x-icon.trash/>
                        </button>
                    </div>
                </template>
                <x-button.primary x-bind:disabled="disabled()" @click="addRow()" class="justify-center">
                    <x-icon.plus/>
                    <span>{{ __('cms.Optie toevoegen') }}</span>
                </x-button.primary>
            </div>

            <div class="flex items-center px-6 pb-6">
                <div class="ml-auto space-x-2">
                    <x-button.text @click="closePopup()" size="sm">
                        <span>{{ __('auth.cancel') }}</span>
                    </x-button.text>
                    <x-button.cta @click="save()" size="sm">
                        <span>{{ __('cms.Toevoegen') }}</span>
                    </x-button.cta>
                </div>
            </div>
        </div>
        <div x-show="showPopup" x-cloak style="height: 70px;"></div>
    </div>
</div>
{{-- @TODO MF 20-02-2023 @ROAN I think the script below can be deleted because we never have a save button on the page--}}
@push('scripts')
    <script>
        if(typeof saveButton === 'undefined') {
            let saveButton = document.querySelector('.save_button')
            if(saveButton) {
                saveButton.addEventListener('click', function () {
                    @this.
                    set("{!!  $attributes->wire('model') !!}", window.ClassicEditors['{{$editorId}}'].getData())
                });
            }
        }
    </script>
@endpush