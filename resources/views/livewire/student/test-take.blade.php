<div x-data="{}" x-init="Core.init(); $wire.showAssignmentElements()" testtakemanager>
    @if ($this->isAllAnswersDone())
        <x-modal maxWidth="lg" wire:model="showTurnInModal">
            <x-slot name="title">{{ __("test-take.Toets inleveren") }}</x-slot>
            <x-slot name="body">{{ __("test-take.Weet je zeker dat je de toets wilt inleveren?") }}</x-slot>
            <x-slot name="actionButton">
                <x-button.cta size="md" onclick="endTest()">
                    <span>{{ __("test-take.Inleveren") }}</span>
                    <x-icon.arrow />
                </x-button.cta>
            </x-slot>
        </x-modal>

    @else
        <x-modal maxWidth="2xl" :customFooter="true" wire:model="showTurnInModal">
            <x-slot name="title">{{ __("test-take.Toets inleveren") }}</x-slot>
            <x-slot name="body">
                <div class="notification warning stretched">
                    <div class="title">{{ __("test-take.attention-not-all-questions-answered") }}</div>
                    <div class="body">
                        <span>{{ __("test-take.not-all-questions-answered") }}</span>
                        <span class="bold">{{ $this->getQuestionNumbersWithNoAnswer() }}</span>
                        <span>{{ __("test-take.not-all-questions-answered-extension") }}</span>
                    </div>
                </div>
            </x-slot>
            <x-slot name="customFooter">
                <div class="flex flex-col w-full px-2.5 gap-2">
                    <div class="flex">
                        {{ __('test-take.Weet je zeker dat je de toets wilt inleveren?') }}
                    </div>
                    <div class="flex self-end gap-4 items-center">
                        <x-button.text size="md" @click="show = false" class="rotate-svg-180">
                            <span>{{ __('modal.cancel') }}</span>
                        </x-button.text>
                        <x-button.cta size="sm" onclick="endTest()">
                            <span>{{ __('test-take.Inleveren') }}</span>
                            <x-icon.checkmark-small />
                        </x-button.cta>
                    </div>
                </div>
            </x-slot>
        </x-modal>
    @endif

    <x-modal maxWidth="lg" wire:model="forceTakenAwayModal" showCancelButton="0">
        <x-slot name="title">{{__('test-take.Toets ingenomen door docent.')}}</x-slot>
        <x-slot name="body">
            @if(!Auth::user()->guest)
                {{ __('test-take.De toets is ingenomen door de docent, je kunt daardoor niet verder werken. Keer terug naar het dashboard.') }}
            @else
                {{ __('test-take.De toets is ingenomen door de docent, je kunt daardoor niet verder werken. Sluit de toets.') }}
            @endif
        </x-slot>
        <x-slot name="actionButton">
            <x-button.cta size="md" onclick="endTest(true)">
                <span>
                    @if(!Auth::user()->guest)
                        {{ __('student.dashboard') }}
                    @else
                        {{ __('general.close') }}
                    @endif
                </span>
                <x-icon.arrow />
            </x-button.cta>
        </x-slot>
    </x-modal>

    @push('scripts')
        <script>
            addCSRFTokenToEcho('{{ csrf_token() }}');

            document.addEventListener("DOMContentLoaded", () => {
                document.renderCounter = 0;
                renderMathML();
                // Livewire.hook('component.initialized', (component) => {})
                // Livewire.hook('element.initialized', (el, component) => {})
                // Livewire.hook('element.updating', (fromEl, toEl, component) => {})
                Livewire.hook("element.updated", (el, component) => {
                    renderMathML();
                });
                // Livewire.hook('element.removed', (el, component) => {})
                // Livewire.hook('message.sent', (message, component) => {});
                Livewire.hook("message.failed", (message, component) => {
                    let container;
                    if (!window.navigator.onLine && message.component.hasOwnProperty("fingerprint") && message.component.fingerprint.name.startsWith("question.")) {
                        const listener = () => {
                            // if(window.navigator.online) {
                            if (container.type == "callMethod") {
                                component.call(container.payload.method, container.payload.params[0]);
                            } else if (container.type == "syncInput") {
                                component.set(container.payload.name, container.payload.value);
                            } else {
                                console.log("no clue what to do with " + container.type);
                            }
                            window.removeEventListener("online", listener);
                            // }
                        };

                        if (message.hasOwnProperty("updateQueue")) {
                            container = message.updateQueue[0];
                        } else if (message.hasOwnProperty("updates")) {
                            container = message.updates[0];
                        }
                        if (container) {
                            window.addEventListener("online", listener);
                        }
                    }

                });
                // Livewire.hook('message.received', (message, component) => {})
                // Livewire.hook('message.processed', (message, component) => {})
                Livewire.hook("beforePushState", (stateObject, url, component) => {
                    fixHistoryApiStateForQueryStringUpdates(stateObject, url)
                });
                const OnlineListener = function() {
                    Notify.notify('{{ __('test-take.your connection is back online') }}', "success");
                    window.removeEventListener("online", OnlineListener);
                    window.addEventListener("offline", Offlinelistener);
                };
                const Offlinelistener = function() {
                    window.addEventListener("online", OnlineListener);
                    window.removeEventListener("offline", Offlinelistener);
                };

                window.addEventListener("offline", Offlinelistener);
            });

            function renderMathML() {
                if ("com" in window && "wiris" in window.com && "js" in window.com.wiris && "JsPluginViewer" in window.com.wiris.js) {
                    com.wiris.js.JsPluginViewer.parseDocument();
                } else {
                    // try again in half a second but no more then for 5 seconds.
                    if (document.renderCounter < 10) {
                        document.renderCounter++;
                        setTimeout(() => renderMathML(), 500);
                    }
                }
            }

            function endTest(forceTaken = false) {
                clearClipboard().then(() => {
                    @this.
                    TurnInTestTake(forceTaken);
                });
            }
        </script>
    @endpush
</div>
