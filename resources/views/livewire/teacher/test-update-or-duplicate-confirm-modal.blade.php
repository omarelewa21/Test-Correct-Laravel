<x-modal.base-modal>
    <x-slot name="title">
        <h2>{{__("teacher.Verbetering of nieuwe toets?")}}</h2>
    </x-slot>
    <x-slot name="content">
        @if($displayValueRequiredMessage)
            <div class="mb-4 text-red-500 text-sm">{{ __('cms.Kies een waarde') }}</div>
        @endif
        <div class="flex " x-data="{
                value : @entangle('value'),
                select: function(option) {
                    this.value = option;
                },
                selected: function(option){
                    return option === this.value;
                },}">
            <div name="block-container" class="grid gap-4 grid-cols-2">
                <div class="col-span-2">
                    {{ __('teacher.Je hebt het vak, niveau en/of leerjaar van de toets aangepast.') }}
                </div>

                <button class="test-change-option transition"
                        :class="{'active': selected('update')}"
                        @click="select('update')"
                >
                    <div class="flex">
                        <x-stickers.test-update/>
                    </div>

                    <div x-show="selected('update')">
                        <x-icon.checkmark class="absolute top-2 right-2 overflow-visible"/>
                    </div>
                    <div class="ml-2.5 text-left">
                        <span class="text-base bold">{{ __('cms.test_verbetering') }}</span>
                        <p class="note text-sm">{{ __('cms.test_verbetering_omschrijving') }}</p>
                    </div>
                </button>

                <button class="test-change-option transition"
                        :class="{'active': selected('duplicate')}"
                        @click="select('duplicate')"
                >
                    <div>
                        <x-stickers.test-new/>
                    </div>
                    <div x-show="selected('duplicate')">
                        <x-icon.checkmark class="absolute top-2 right-2 overflow-visible"/>
                    </div>
                    <div class="ml-2.5 text-left" >
                        <span class="text-base bold">{{ __('cms.test_nieuw') }}</span>
                        <p class="note text-sm">{{ __('cms.test_nieuw_omschrijving') }}</p>
                    </div>
                </button>
            </div>
        </div>
    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-end items-center">
            <div class="flex gap-4 items-center">
                <x-button.text wire:click="close">
                    <span>{{ __('teacher.Annuleer') }}</span>
                </x-button.text>
                <x-button.cta wire:click="submit" size="md">
                    <span>{{ __('Bevestigen') }}</span>
                </x-button.cta>
            </div> {{-- 44vw depends on maxWidth 2xl... --}}
        </div>
    </x-slot>
</x-modal.base-modal>
