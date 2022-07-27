<x-modal.confirmation wire:model="showModal">
    <x-slot name="title">{{ __('teacher.Toets verwijderen') }}</x-slot>
    <x-slot name="body">{{ __('teacher.Weet je zeker dat je deze toets wilt verwijderen? Deze actie kun je niet ongedaan maken.') }}</x-slot>
    <x-slot name="actionButton">
        <x-button.text-button wire:click="$set('showModal', false)">{{ __('teacher.Annuleer') }}</x-button.text-button>
        <x-button.primary wire:click="deleteTest">{{ __('teacher.Verwijder') }}</x-button.primary>
    </x-slot>
</x-modal.confirmation>
