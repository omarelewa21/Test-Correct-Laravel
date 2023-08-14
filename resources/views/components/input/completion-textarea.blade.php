@props([
'type' => 'cms-completion',
'editorId',
'disabled' => false,
'lang' => 'nl_NL',
'allowWsc' => false,
])
<div class="relative"
     x-data="completionOptions({ value: $wire.entangle('showSelectionOptionsModal'), editorId: '{{ $editorId }}' })"
     @initwithselection.window="initWithCompletion()"
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
                    <span class="bold text-base">{{ __('cms.Gatentekst toevoegen') }}</span>
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