<x-modal.base-modal>
    <x-slot:title>
        <h2>@lang('general.attention')</h2>
    </x-slot:title>

    <x-slot:content>
        <div>
            @lang('cms.relation-question-confirm-adding-text')
        </div>
    </x-slot:content>
    <x-slot:footer>
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
                <x-input.checkbox wire:model="dontShowAgain"/>
                <span class="bold">@lang('general.Toon niet opnieuw')</span>
            </div>
            <div class="flex items-center gap-4">
                <x-button.text wire:click="$emit('closeModal')">
                    <span>@lang('modal.annuleren')</span>
                </x-button.text>
                <x-button.cta size="md" wire:click="continue">
                    <span>@lang('cms.Ik begrijp het')</span>
                </x-button.cta>
            </div>
        </div>
    </x-slot:footer>
</x-modal.base-modal>

