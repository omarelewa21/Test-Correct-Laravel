<x-modal.base-modal wire:model="showModal">
    <x-slot name="title"><h2>{{ __('teacher.Toets verwijderen') }}</h2></x-slot>
    <x-slot name="content">{{ __('teacher.Weet je zeker dat je deze toets wilt verwijderen? Deze actie kun je niet ongedaan maken.') }}</x-slot>
    <x-slot name="footer">
        <div class="flex justify-end w-full gap-4">
            <x-button.text-button selid="test-delete-modal-cancel"
                                  wire:click="$emit('closeModal')">
                <span>{{ __('teacher.Annuleer') }}</span>
            </x-button.text-button>

            <x-button.cta selid="test-delete-modal-confirm" wire:click="deleteTest"><span>{{ __('cms.delete') }}</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>
