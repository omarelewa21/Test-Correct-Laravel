<x-menu.context-menu.base context="test-card">
    @if($testUuid)
        <div wire:key="test-context-menu-buttons-{{ $testUuid }}"
             class="flex flex-col"
        >
        @if($showNonPublicItems)
            <livewire:actions.test-plan-test :wire:key="'plan-test-'.$testUuid" :uuid="$testUuid" variant="context-menu" class="order-1"/>
            <livewire:actions.test-quick-take :wire:key="'quick-take-'.$testUuid" :uuid="$testUuid" variant="context-menu" class="order-2"/>
            <livewire:actions.test-make-pdf :wire:key="'make-pdf-'.$testUuid" :uuid="$testUuid" variant="context-menu" class="order-4"/>
            <x-actions.test-open-edit :uuid="$testUuid" variant="context-menu" class="order-6"/>
            <x-actions.test-open-settings :uuid="$testUuid" variant="context-menu" class="order-7"/>
            <x-actions.test-delete :uuid="$testUuid" variant="context-menu" class="order-8"/>
        @endif
            <livewire:actions.test-duplicate-test :wire:key="'duplicate-test-'.$testUuid" :uuid="$testUuid" variant="context-menu" class="order-3"/>
            <x-actions.test-open-preview :uuid="$testUuid" variant="context-menu" class="order-5"/>
        </div>
    @endif
</x-menu.context-menu.base>