<div x-ref="contextMenu"
     x-cloak
     x-show="showMenu"
     x-data="{
            showMenu: false,
            correspondingButton: null,
            handleIncomingEvent(detail) {
                if (!this.showMenu) return this.openMenu(detail);

                this.closeMenu(false);
                setTimeout(() => {
                    this.openMenu(detail);
                }, 200);
            },
            async openMenu(detail) {
                this.$root.style.top = (detail.top + 52 ) + 'px';
                this.$root.style.left = (detail.left - 224) + 'px';
                this.correspondingButton = detail.button;
                this.uuid = detail.testUuid;
                let readyForShow = await this.$wire.setContextValues(detail.testUuid, detail.openTab);
                if (readyForShow) this.showMenu = true;
            },
            closeMenu(clearUuid = true) {
                this.correspondingButton.dispatchEvent(new CustomEvent('close-menu'));
                this.showMenu = false;
                if (clearUuid) {
                    this.$wire.clearContextValues();
                }
            }
        }"

     @test-card-context-menu-close.window="closeMenu();"
     @test-card-context-menu-show.window="handleIncomingEvent($event.detail)"
     @click.outside="closeMenu();"
     class="absolute w-50 bg-white py-2 main-shadow rounded-10 z-1"
     x-transition:enter="transition ease-out origin-top-right duration-200"
     x-transition:enter-start="opacity-0 transform scale-90"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition origin-top-right ease-in duration-100"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-90"
>
    @if($testUuid)
        <div wire:key="test-context-menu-buttons-{{ $testUuid }}"
             @click="closeMenu()"
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
</div>