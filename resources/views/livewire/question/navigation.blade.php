<div class="flex flex-col pt-4 pb-4 space-y-10" test-take-player wire:key="navigation">
    <x-partials.question-indicator wire:key="navi" :nav="$nav"></x-partials.question-indicator>

    <x-modal maxWidth="lg" wire:model="showTurnInModal">
        <x-slot name="title">Toets inleveren</x-slot>
        <x-slot name="body">Weet je zeker dat je de toets wilt inleveren?</x-slot>
        <x-slot name="actionButton">
            <x-button.cta size="md" wire:click="toOverview">
                <span>Inleveren</span>
                <x-icon.arrow/>
            </x-button.cta>
        </x-slot>
    </x-modal>
</div>
