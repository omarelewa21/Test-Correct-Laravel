<div id="planned-body"
     x-data="{startCountdown: false, isTakeOpen: @entangle('isTakeOpen'), countdownNumber: {{ $this->getCountdownNumber() }}, activeStudents: 0, presenceChannel: null}"
     x-init="
        addRelativePaddingToBody('planned-body');
        @if(!Auth::user()->guest)
        makeHeaderMenuActive('student-header-tests');
        @endif

        presenceChannel = Echo.join('presence-TestTake.{{ $waitingTestTake->uuid }}');
        presenceChannel.here((users) => {
            activeStudents = countPresentStudents(presenceChannel.subscription.members);
        }).joining((user) => {
            activeStudents = countPresentStudents(presenceChannel.subscription.members);
        }).leaving((user) => {
            activeStudents = countPresentStudents(presenceChannel.subscription.members);
        });
        presenceChannel.listen('.TestTakeShowResultsChanged', (e) => {
            Livewire.emit('is-test-take-open', e)
        });
     "
     x-ref="root"
     x-cloak
     class="w-full flex flex-col items-center student-bg"
     :class="{'overflow-hidden h-screen' : startCountdown}"
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('planned-body')"
     wire:ignore.self
     wire:init="isTestTakeOpen"
>
    <div class="flex w-full justify-center border-b border-system-secondary transition-all duration-500"
         :class="{'opacity-0': startCountdown}">
        <x-partials.test-take-sub-menu :active="$this->testTakeStatusStage" :disabled="Auth::user()->guest"/>
    </div>
    <div class="flex flex-col w-full mt-10">
        <div class="w-full px-4 lg:px-8 xl:px-24 transition-all duration-500">
            <div class="flex flex-col space-y-4 transition-all duration-500">
                <div>
                    @if(!Auth::user()->guest)
                        <x-button.text-button class="rotate-svg-180" type="link"
                                              href="{{ route('student.test-takes', ['tab' => $this->testTakeStatusStage]) }}">
                            <x-icon.arrow/>
                            <span class="text-[32px]" selid="waiting-screen-title">{{ $waitingTestTake->test_name }}</span>
                        </x-button.text-button>
                    @elseif(Auth::user()->guest && $this->testTakeStatusStage != 'planned')
                        <x-button.text-button class="rotate-svg-180" wire:click="returnToGuestChoicePage">
                            <x-icon.arrow/>
                            <span class="text-[32px]" selid="waiting-screen-title">{{ $waitingTestTake->test_name }}</span>
                        </x-button.text-button>
                    @else
                        <span class="bold text-[32px]" selid="waiting-screen-title">{{ $waitingTestTake->test_name }}</span>
                    @endif
                </div>
                <div>
                    <x-partials.waiting-room-grid :waitingTestTake="$waitingTestTake"
                                                  :participatingClasses="$participatingClasses"/>
                </div>
                <div class="flex w-full items-center h-10">
                    @if(!$needsApp)
                        <x-partials.waiting-room-action-button
                           :testTakeStatusStage="$this->testTakeStatusStage"
                           :isTakeOpen="$this->isTakeOpen"
                        />
                    @else
                        @if($meetsAppRequirement)
                        <x-partials.waiting-room-action-button
                            :testTakeStatusStage="$this->testTakeStatusStage"
                            :isTakeOpen="$this->isTakeOpen"
                        />
                        @else
                            <div class="divider flex flex-1"></div>
                            <div class="flex flex-col justify-center">
                                <x-button.cta disabled class="mx-4">
                                    <span>{{ __('Toets starten niet mogelijk') }}</span>
                                </x-button.cta>
                            </div>
                            <div class="divider flex flex-1"></div>
                        @endif
                    @endif

                </div>
                <div class="flex w-full justify-center transition-all duration-300"
                     :class="{'opacity-50' : isTakeOpen}">
                    <x-illustrations.waiting-room/>
                </div>

            </div>
        </div>
        <div class="flex flex-col bg-light-grey items-center justify-center py-12">
            <div class="content-section flex flex-col w-full max-w-2xl p-8 space-y-4">
                @if($this->testTakeStatusStage != 'graded')
                    @if($this->testTakeStatusStage == 'planned')
                        <h4 class="px-3">{{ __('student.teacher_introduction_title') }}</h4>
                        <div class="divider"></div>
                        <div class="px-3">
                            @if($isTakeOpen)
                                {!! nl2br($waitingTestTake->test->introduction) ?: __('student.teacher_introduction_unavailable') !!}
                            @else
                                {{ __('student.teacher_introduction_long') }}
                            @endif
                        </div>
                    @else
                        <h4 class="px-3">{{ __('student.teacher_introduction_not_available') }}</h4>
                        <div class="divider"></div>
                        <div class="px-3">
                            {{ __('student.teacher_introduction_not_available_long') }}
                        </div>
                    @endif
                @else
                    <h4 class="px-3">{{ __('student.your_grade') }}</h4>
                    <div class="divider"></div>
                    <div class="">
                        <div class="relative w-full flex hover:font-bold py-5 px-3 rounded-10 base
                                    multiple-choice-question transition ease-in-out duration-150 focus:outline-none
                                    justify-between items-center -mt-4"
                        >
                            <span>{{ auth()->user()->getNameFullAttribute() }}</span>
                            @if(!$showGrades)
                                <span title="{{__('test_take.hide_grade_tooltip')}}">
                                    {{ __('test_take.nvt') }}
                                </span>
                            @elseif($testParticipant->rating)
                            <span class="px-2 py-1 text-sm rounded-full {!! $this->getBgColorForTestParticipantRating($this->getRatingToDisplay($testParticipant)) !!}">
                                {{ $this->getRatingToDisplay($testParticipant) }}
                            </span>
                            @else
                                <x-icon.time-dispensation class="text-inherit"/>
                            @endif
                        </div>
                    </div>
                @endif
                @if($needsApp && !$meetsAppRequirement && !$this->testParticipant->isInBrowser())
                    <div class="flex w-full justify-center transition-all duration-300 mb-4">
                        <div class="notification error stretched">
                            <div class="flex items-center space-x-3">
                                <x-icon.exclamation/>
                                <span class="title">{{ __('general.attention') }}</span>
                            </div>
                            <span class="body">{{ __('student.app_not_allowed') }}</span>
                        </div>
                    </div>
                @endif
                @if($needsApp && $this->testParticipant->isInBrowser())
                    <div class="flex w-full justify-center transition-all duration-300 mb-4">
                        <div class="notification error stretched">
                            <div class="flex items-center space-x-3">
                                <x-icon.exclamation/>
                                <span class="title">{{ __('auth.download_student_app') }}</span>
                            </div>
                            <span class="body">{{ __('student.not_allowed_to_test_in_browser') }}</span>
                        </div>
                    </div>
                @endif
                @if($needsApp && $appNeedsUpdate)
                    <div class="flex w-full justify-center transition-all duration-300 mb-4">
                        <div class="notification warning stretched">
                            <div class="flex items-center space-x-3">
                                <x-icon.exclamation/>
                                <span class="title">{{ __('general.attention') }}!</span>
                            </div>
                            @if($appNeedsUpdateDeadline)
                                <span class="body">{{ __('student.app_needs_update_deadline', ['date' => $appNeedsUpdateDeadline]) }}</span>
                            @else
                                <span class="body">{{ __('student.app_needs_update') }}</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="w-full bg-white fixed bottom-0 footer-shadow transition-all duration-500 z-10"
         :class="{'pb-16 pt-1.5' : startCountdown}">
    </div>

    <div class="fixed student-bg top-0 left-0 h-full w-full transition-all duration-500 py-[70px]"
         x-show.transition.500ms="startCountdown"
    >
        <div class="w-full h-full px-4 lg:px-8 xl:px-12 transition-all duration-500">
            <div class="flex h-full flex-col mx-auto max-w-7xl transition-all duration-500 pt-16">
                <div class="flex flex-col mb-4">
                    <span class="-mb-2">{{ __('student.planned_test') }}</span>
                    <x-button.text-button class="rotate-svg-180"
                                          x-on:click="startCountdown = false; stopCountdownTimer($refs.root._x_dataStack[0])">
                        <x-icon.arrow/>
                        <span class="text-[32px]" selid="waiting-screen-title">{{ $waitingTestTake->test->name }}</span>
                    </x-button.text-button>
                </div>
                <div class="flex flex-col flex-1 w-full items-center mt-16 space-y-3">
                    <span>{{ __('student.test_starts_in') }}</span>
                    <div class="flex w-28 h-28 justify-center items-center text-center bold border-4 border-system-base rounded-full"
                         style="font-size: 64px">
                        <span class="w-full" x-text="countdownNumber"></span>
                    </div>
                </div>
                <div class="flex w-full mt-auto opacity-50 justify-center">
                    <x-illustrations.waiting-room/>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let countdownTimer;

            function startCountdownTimer(data) {
                countdownTimer = setInterval(function () {
                    data.countdownNumber -= 1;
                    if (data.countdownNumber === 0) {
                        Core.setAppTestConfigIfNecessary('{{ $testParticipant->uuid }}');
                        clearClipboard().then(()=>{
                            Livewire.emitTo('student.waiting-room', 'start-test-take');
                        });
                        clearInterval(countdownTimer);
                    }
                }, 1000);
            }

            function stopCountdownTimer(data) {
                clearInterval(countdownTimer);
                setTimeout(function () {
                    data.countdownNumber = {{ $this->getCountdownNumber() }}
                }, 100)
            }
        </script>
    @endpush
</div>
