<div x-data="" testtakemanager>
    <x-modal maxWidth="lg" wire:model="showTurnInModal">
        <x-slot name="title">{{ __("test-take.Toets inleveren") }}</x-slot>
        <x-slot name="body">{{ __("test-take.Weet je zeker dat je de toets wilt inleveren") }}?</x-slot>
        <x-slot name="actionButton">
            <x-button.cta size="md" wire:click="TurnInTestTake">
                <span>{{ __("test-take.Inleveren") }}</span>
                <x-icon.arrow/>
            </x-button.cta>
        </x-slot>
    </x-modal>

    <x-notification/>
</div>
