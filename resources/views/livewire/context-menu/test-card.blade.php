<x-context-menu-base context="test-card">
    @if($testUuid)
        <div wire:key="test-context-menu-buttons-{{ $testUuid }}"
             class="flex flex-col"
        >
            @if($openTab !== 'umbrella')
                <livewire:actions.test-plan-test :uuid="$testUuid" variant="context-menu" class="order-1"/>
                <livewire:actions.test-make-pdf :uuid="$testUuid" variant="context-menu" class="order-3"/>
                <x-actions.test-open-edit :uuid="$testUuid" variant="context-menu" class="order-5"/>
                <x-actions.test-open-settings :uuid="$testUuid" variant="context-menu" class="order-6"/>
                <x-actions.test-delete :uuid="$testUuid" variant="context-menu" class="order-7"/>
            @endif

            <livewire:actions.test-duplicate-test :uuid="$testUuid" variant="context-menu" class="order-2"/>
            <x-actions.test-open-preview :uuid="$testUuid" variant="context-menu" class="order-4"/>

        </div>
    @endif
</x-context-menu-base>