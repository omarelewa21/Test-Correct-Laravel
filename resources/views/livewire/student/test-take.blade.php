<div x-data="{}" testtakemanager>
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
            parent.skip = false;
            var notifsent = false;
            var lastLostFocus = {notification: false, delay: 3 * 60, reported: {}};
            var alert = false;
            var checkFocusTimer = false;

            var Notify = {
                notify: function (message, type) {
                    var type = type ? type : 'info';
                    window.dispatchEvent(new CustomEvent('notify', {detail: {message, type}}))
                }
            }

            function runCheckFocus() {
                if (!checkFocusTimer) {
                    checkFocusTimer = setInterval(checkPageFocus, 300);
                }
            }

            runCheckFocus();

            function checkPageFocus() {
                if (!parent.skip) {
                    if (!document.hasFocus()) {
                        if (!notifsent) {  // checks for the notifcation if it is already sent to the teacher
                            console.log('lost focus from checkPageFocus');
                            lostFocus('lost-focus');
                            notifsent = true;
                        }
                    } else {
                        notifsent = false;  //mark it as not sent, to active it again
                    }
                } else {
                    window.focus();   //we need to set focus back to the window before changing skip value
                    parent.skip = false;
                }
            }

            function lostFocus(reason) {
                if (reason == "printscreen") {
                    Notify.notify({{__('test-take.Het is niet toegestaan om een screenshot te maken, we hebben je docent hierover geïnformeerd')}}, 'error');
                } else {
                    Notify.notify({{__('test-take.Het is niet toegestaan om uit de app te gaan')}}, 'error');
                }

                if (shouldLostFocusBeReported(reason)) {
                    livewire.find(document.querySelector('[testtakemanager]').getAttribute('wire:id')).call('createTestTakeEvent', reason);
                }
                alert = true;
            }

            function shouldLostFocusBeReported(reason) {

                if (reason == null) {
                    reason == "undefined";
                }

                if (!(reason in lastLostFocus.reported) || !alert) {
                    lastLostFocus.reported[reason] = (new Date()).getTime() / 1000;
                    return true;
                }

                var now = (new Date()).getTime() / 1000;
                var lastTime = lastLostFocus.reported[reason];
                if (lastTime <= now - lastLostFocus.delay) {
                    lastLostFocus.reported[reason] = (new Date()).getTime() / 1000;
                    return true;
                }

                return false;
            }

        </script>
    @endpush
</div>
