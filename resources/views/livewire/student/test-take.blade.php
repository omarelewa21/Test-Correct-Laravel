<div x-data="{}" x-init="runCheckFocus;" testtakemanager>
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

    <x-modal maxWidth="lg" wire:model="forceTakenAwayModal" showCancelButton="0">
        <x-slot name="title">Toets ingenomen door docent.</x-slot>
        <x-slot name="body">De toets is ingenomen door de docent, je kunt daardoor niet verder werken. Keer terug naar
            het dashboard.
        </x-slot>
        <x-slot name="actionButton">
            <x-button.cta size="md" wire:click="TurnInTestTake">
                <span>Dashboard</span>
                <x-icon.arrow/>
            </x-button.cta>
        </x-slot>
    </x-modal>

    <x-notification :notificationTimeout="$notificationTimeout"/>
</div>
