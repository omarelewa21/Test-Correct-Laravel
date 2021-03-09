<div x-data="" testtakemanager>
    <x-modal maxWidth="lg" wire:model="showTurnInModal">
        <x-slot name="title">Toets inleveren</x-slot>
        <x-slot name="body">Weet je zeker dat je de toets wilt inleveren?</x-slot>
        <x-slot name="actionButton">
            <x-button.cta size="md" wire:click="TurnInTestTake">
                <span>Inleveren</span>
                <x-icon.arrow/>
            </x-button.cta>
        </x-slot>
    </x-modal>

    <x-notification :notificationTimeout="$notificationTimeout"/>
</div>
