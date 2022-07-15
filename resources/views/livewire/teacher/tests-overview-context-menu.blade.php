<div>
@if ( $this->displayMenu)
    <div
        x-ref="contextMenu"
        x-cloak
        x-show="show"
        x-data = "{
            show: @entangle('displayMenu'),
            posX: @js($x),
            posY: @js($y),

            init() {
                $nextTick(() => {
                    $refs.contextMenu.style.top = (this.posY + 36 ) + 'px';
                    $refs.contextMenu.style.left = (this.posX - (this.$el.offsetWidth - 36)) + 'px';
                })
            }
        }"

         x-on:context-menu-close="show = false"
         class="fixed bg-white py-2 main-shadow rounded-10 w-72 z-30 "
         @click.outside="show = false;"
         x-transition:enter="transition ease-out origin-top-right duration-200"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition origin-top-right ease-in duration-100"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90"
         @click="testOptionMenu=false"
    >

        <livewire:actions.test-plan-test :uuid="$test->uuid" variant="context-menu" />
        <livewire:actions.test-duplicate-test :uuid="$test->uuid" variant="context-menu" />
        <livewire:actions.test-make-pdf :uuid="$test->uuid" variant="context-menu"/>
        <x-actions.test-open-preview :uuid="$test->uuid" variant="context-menu"/>
        <x-actions.test-open-edit :uuid="$test->uuid" variant="context-menu"/>
        <x-actions.test-open-settings :uuid="$test->uuid" variant="context-menu" />
        <x-actions.test-delete :uuid="$test->uuid" variant="context-menu" />

    </div>
@endif
</div>
