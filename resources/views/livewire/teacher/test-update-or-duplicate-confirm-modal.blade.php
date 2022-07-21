<x-modal-new>
    <x-slot name="title">
        {{__("teacher.Verbetering of nieuwe toets?")}}
    </x-slot>
    <x-slot name="body">
        @if($displayValueRequiredMessage)
            <div class="mt-1 text-red-500 text-sm">{{ __('Selecteer een waarde') }}</div>
        @endif
        <div class="flex px-1" x-data="{
                value : @entangle('value'),
                select: function(option) {
                    this.value = option;
                },
                selected: function(option){
                    return option === this.value;
                },}">
            <div name="block-container" class="grid grid-cols-2 pt-5">
                <div class="col-span-2 mb-6">
                    Je hebt het type, niveau en/of leerjaar van de toets aangepast. Is deze verandering een verbetering
                    van de toets of is het een nieuwe toets?
                </div>

                <button class="group-type mr-2 mb-2 "
                        :class="selected('update') ? 'active' : 'hover:shadow-lg'"
                        @click="select('update')"
                        @isset($preview) disabled @endisset
                >
                    <div class="flex">
                        <x-stickers.test-update/>
                    </div>

                    <div x-show="selected('update')">
                        <x-icon.checkmark-circle class="absolute top-2 right-2 overflow-visible"/>
                    </div>
                    <div class="-mt-1 ml-2.5 text-left">
                        <span
                            :class="selected('update') ? 'text-primary' : 'text-sysbase'">{{ __('cms.test_verbetering') }}</span>
                        <p class="note text-sm">{{ __('cms.test_verbetering_omschrijving') }}</p>
                    </div>
                </button>

                <button class="group-type mb-2"
                        :class="selected('duplicate') ? 'active' : 'hover:shadow-lg'"
                        @click="select('duplicate')"
                        @isset($preview) disabled @endisset
                >
                    <div>
                        <x-stickers.test-new/>
                    </div>
                    <div x-show="selected('duplicate')">
                        <x-icon.checkmark-circle class="absolute top-2 right-2 overflow-visible"/>
                    </div>
                    <div class="-mt-1 ml-2.5 text-left">
                        <span
                            :class="selected('duplicate') ? 'text-primary' : 'text-sysbase'">{{ __('cms.test_nieuw') }}</span>
                        <p class="note text-sm">{{ __('cms.test_nieuw_omschrijving') }}</p>
                    </div>
                </button>
            </div>
    </x-slot>
    <x-slot name="footer">
        <div class="w-[44vw] flex justify-end items-center">
            <div class="mt-8 pr-12 space-x-2.5">
                <x-button.text-button wire:click="close">{{ __('teacher.Annuleer') }}</x-button.text-button>
                <x-button.cta wire:click="submit">{{ __('Bevestigen') }}</x-button.cta>
            </div> {{-- 44vw depends on maxWidth 2xl... --}}
        </div>
    </x-slot>
</x-modal-new>
