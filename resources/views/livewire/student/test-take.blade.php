<div x-data="{}" x-init="runCheckFocus;" testtakemanager>
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

    <x-modal maxWidth="lg" wire:model="forceTakenAwayModal" showCancelButton="0">
        <x-slot name="title">{{__('test-take.Toets ingenomen door docent.')}}</x-slot>
        <x-slot name="body">{{__('test-take.De toets is ingenomen door de docent, je kunt daardoor niet verder werken. Keer terug naar het dashboard.')}}
        </x-slot>
        <x-slot name="actionButton">
            <x-button.cta size="md" wire:click="TurnInTestTake">
                <span>Dashboard</span>
                <x-icon.arrow/>
            </x-button.cta>
        </x-slot>
    </x-modal>

    <x-notification :notificationTimeout="$notificationTimeout"/>
    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                document.renderCounter = 0;
                renderMathML();
                // Livewire.hook('component.initialized', (component) => {})
                // Livewire.hook('element.initialized', (el, component) => {})
                // Livewire.hook('element.updating', (fromEl, toEl, component) => {})
                Livewire.hook('element.updated', (el, component) => {
                    renderMathML()
                });
                // Livewire.hook('element.removed', (el, component) => {})
                // Livewire.hook('message.sent', (message, component) => {})
                // Livewire.hook('message.failed', (message, component) => {})
                // Livewire.hook('message.received', (message, component) => {})
                // Livewire.hook('message.processed', (message, component) => {})
            });

            function renderMathML() {
                if ('com' in window && 'wiris' in window.com && 'js' in window.com.wiris && 'JsPluginViewer' in window.com.wiris.js) {
                    com.wiris.js.JsPluginViewer.parseDocument();
                } else {
                    // try again in half a second but no more then for 5 seconds.
                    if (document.renderCounter < 10) {
                        document.renderCounter++;
                        setTimeout(() => renderMathML(), 500);
                    }
                }
            }
        </script>
    @endpush
</div>
