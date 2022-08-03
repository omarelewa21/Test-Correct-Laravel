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
                let readyForShow = await this.$wire.setUuid(detail.testUuid);
                if (readyForShow) this.showMenu = true;
            },
            closeMenu(clearUuid = true) {
                this.correspondingButton.dispatchEvent(new CustomEvent('close-menu'));
                this.showMenu = false;
                if (clearUuid) {
                    this.$wire.set('testUuid', '');
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
        >
            <livewire:actions.test-plan-test :uuid="$testUuid" variant="context-menu"/>
            <livewire:actions.test-duplicate-test :uuid="$testUuid" variant="context-menu"/>
            <livewire:actions.test-make-pdf :uuid="$testUuid" variant="context-menu"/>
            <x-actions.test-open-preview :uuid="$testUuid" variant="context-menu"/>
            <x-actions.test-open-edit :uuid="$testUuid" variant="context-menu"/>
            <x-actions.test-open-settings :uuid="$testUuid" variant="context-menu"/>
            <x-actions.test-delete :uuid="$testUuid" variant="context-menu"/>
        </div>
    @endif
</div>