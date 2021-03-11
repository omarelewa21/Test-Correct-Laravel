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

    <script>
        var Notify = {
            notify: function (message, type) {
                var type = type ? type : 'info';
                window.dispatchEvent(new CustomEvent('notify', {detail: {message, type}}))
            }
        }

        var alert = false;
        var checkFocusTimer = false;
        function runCheckFocus() {
            if (!checkFocusTimer) {
                checkFocusTimer = setInterval(checkPageFocus, 300);
            }
        }

        parent.skip = false;
        var notifsent = false;
        var lastLostFocus = { notification: false, delay: 3*60, reported: {} };

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
                Notify.notify('Het is niet toegestaan om een screenshot te maken, we hebben je docent hierover geïnformeerd', 'error');
            } else {
                Notify.notify('Het is niet toegestaan om uit de app te gaan', 'error');
            }

            if (shouldLostFocusBeReported(reason)) {
                Livewire.emitTo('student.fraud-detection', 'create-test-take-event', reason)
            }
            alert = true;
        }

        function shouldLostFocusBeReported(reason) {

            if (reason == null) {
                reason == "undefined";
            }


            if (!(reason in lastLostFocus.reported) || !alert) {
                lastLostFocus.reported[reason] = (new Date()).getTime()/1000;
                return true;
            }

            var now = (new Date()).getTime()/1000;
            var lastTime = lastLostFocus.reported[reason];
            if (lastTime <= now - lastLostFocus.delay) {
                lastLostFocus.reported[reason] = (new Date()).getTime()/1000;
                return true;
            }

            return false;
        }

    </script>
</div>
