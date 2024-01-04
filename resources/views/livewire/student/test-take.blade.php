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

            document.addEventListener('livewire:load', function () {
                document.renderCounter = 0;
                renderMathML();
                Livewire.hook("element.updated", (el, component) => {
                    renderMathML();
                });
                let syncContainer = {};
                Livewire.hook("message.failed", (message, component) => {
                    // inform the application on the client side that we are offline
                    // save the call if it is a sync to do that afterwards when back online
                    // start polling to see whether we are online again

                    let container;
                    reinitLivewireComponent(component.el);
                    let electronNotified = false;
                    if (message.component.hasOwnProperty("fingerprint") && message.component.fingerprint.name.startsWith("student-player.question.")) {
                        container = getContainerFromLivewireMessage(message);
                        if(!container) return;
                        if (container.type == "syncInput") {
                            syncContainer[container.payload.name] = {componentId: component.id, value: container.payload.value};
                            let electronData = {
                                'tpId': '{{ $this->testParticipantUuid }}',
                                'ttId': '{{ $this->testTakeUuid }}',
                            }
                            electronData[container.payload.name] = container.payload.value;
                            notifyElectronOffline(electronData);
                            electronNotified = true;
                        } else {
                            console.log("we don't do any thing with this type at the moment: " + container.type);
                        }
                    }

                    if(!electronNotified) {
                        try {
                            electron.logNetworkFailure({
                                'tpId': '{{ $this->testParticipantUuid }}',
                                'ttId': '{{ $this->testTakeUuid }}'
                            })
                        } catch (error) {
                            console.log('Not in mac/windows app or using older version of the app')
                        }
                    }
                    handleWhileOffline();
                });

                function notifyElectronOffline(data) {
                    try {
                        electron.logNetworkFailure(data)
                    } catch (error) {
                        console.log('Not in mac/windows app or using older version of the app')
                    }
                }
                let handleWhileOfflineTimer;
                function handleWhileOffline(){
                    // show offline message and block all navigation
                    clearTimeout(handleWhileOfflineTimer);
                    var backOnline = false;
                    fetch("/robots.txt")
                        .then(response => {
                            if (response.ok || response.status == 404) {
                                clearTimeout(handleWhileOfflineTimer);
                                backOnline = true;
                                handleWhenBackOnline();

                                return;
                            }
                        })
                        .catch(reason => {
                            if(backOnline) return;
                            handleWhileOfflineTimer = setTimeout(function(){
                                handleWhileOffline();
                            },1000);
                        });
                }

                function handleWhenBackOnline(){
                    clearTimeout(handleWhileOfflineTimer);
                    let properties = Object.getOwnPropertyNames(syncContainer);
                    properties.forEach((val, idx, array) => {
                        if(val && syncContainer[val] != undefined){
                            // this one needs to be synced still
                            if(Livewire.find(syncContainer[val].componentId) != undefined) {
                                Livewire.find(syncContainer[val].componentId).set(val, syncContainer[val].value);
                            }
                            // @this.set(val,syncContainer[val]);
                            delete syncContainer[val];
                        }
                    });
                    // show back online
                    Notify.notify('{{ __('test-take.your connection is back online') }}', "success");
                }
                Livewire.hook("message.received",(message, component) => {

                    let container = getContainerFromLivewireMessage(message);
                    if(!container) return;
                    if(container.type == "syncInput"){
                        if(syncContainer.hasOwnProperty(container.payload.name)){
                            delete syncContainer[container.payload.name];
                        }
                    }
                });
                // Livewire.hook('message.received', (message, component) => {})
                // Livewire.hook('message.processed', (message, component) => {})
                Livewire.hook("beforePushState", (stateObject, url, component) => {
                    fixHistoryApiStateForQueryStringUpdates(stateObject, url)
                });

            });

            function getContainerFromLivewireMessage(message){
                if (message.hasOwnProperty("updateQueue")) {
                    return message.updateQueue[0];
                } else if (message.hasOwnProperty("updates")) {
                    return message.updates[0];
                }
                return false;
            }

            function reinitLivewireComponent(component) {
                // console.log('reinitLivewireComponent');
                // if(component.hasOwnProperty("__livewire")) {
                //     console.log('has __livewire');
                //     // Remove all livewire events
                //     component.__livewire.tearDown();
                //     // Re-add all livewire events
                //     component.__livewire.initialize();
                //     console.log('re initialized');
                // }
            }

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
                    @this.TurnInTestTake(forceTaken);
                });
            }
        </script>
    @endpush
</div>
