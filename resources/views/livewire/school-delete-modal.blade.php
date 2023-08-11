<x-modal.base-modal wire:model="showModal">
    <x-slot name="title"><h2>{{ __('school.delete_school') }}</h2></x-slot>
    <x-slot name="content">{{ __('school.delete_are_you_sure') }}</x-slot>
    <x-slot name="footer">
        <div class="flex justify-end w-full gap-4 items-center">
            <x-button.text wire:click="$emit('closeModal')"><span>{{ __('teacher.Annuleer') }}</span>
            </x-button.text>

            <x-button.cta wire:click="delete"><span>{{ __('cms.delete') }}</span></x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>
